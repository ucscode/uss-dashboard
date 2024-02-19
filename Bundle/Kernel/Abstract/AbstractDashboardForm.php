<?php

namespace Module\Dashboard\Bundle\Kernel\Abstract;

use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormBuilderInterface;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormInterface;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormSubmitInterface;
use Ucscode\UssForm\Collection\Collection;
use Ucscode\UssForm\Field\Field;
use Ucscode\UssForm\Form\Attribute;
use Ucscode\UssForm\Form\Form;
use Ucscode\UssForm\Gadget\Gadget;
use Ucscode\UssForm\Resource\Facade\Position;
use Ucscode\UssForm\Resource\Service\Pedigree\FieldPedigree;
use Uss\Component\Block\BlockManager;

abstract class AbstractDashboardForm extends Form implements DashboardFormInterface
{
    abstract protected function buildForm(): void;
    abstract protected function validateResource(array $filteredResource): ?array;
    abstract protected function persistResource(?array $validatedResource): mixed;
    abstract protected function resolveSubmission(mixed $presistedResource): void;

    public readonly Collection $collection;
    private array $properties = [];
    private array $submitInterfaces = [];
    private array $builderInterfaces = [];
    protected ?bool $replaceState = true;

    public function __construct(Attribute $attribute = new Attribute())
    {
        parent::__construct($attribute);
        $this->collection = $this->getCollection(self::DEFAULT_COLLECTION);
        $classBase = basename(str_replace("\\", "/", get_called_class()));
        $this->element->setAttribute('id', strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $classBase)));
    }

    public function isSubmitted(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nonce'] ?? false);
    }

    final public function setProperty(string $name, mixed $property): self
    {
        $this->properties[$name] = $property;
        return $this;
    }

    final public function getProperty(string $name): mixed
    {
        return $this->properties[$name] ?? null;
    }

    final public function removeProperty($name): self
    {
        if(array_key_exists($name, $this->properties, true)) {
            unset($this->properties[$name]);
        }
        return $this;
    }

    final public function getProperties(): array
    {
        return $this->properties;
    }

    final public function addBuilderAction(string $name, DashboardFormBuilderInterface $builder): self
    {
        if(!in_array($builder, $this->builderInterfaces, true)) {
            $this->builderInterfaces[$name] = $builder;
        }
        return $this;
    }

    final public function getBuilderAction(string $name): ?DashboardFormBuilderInterface
    {
        return $this->builderInterfaces[$name] ?? null;
    }

    final public function removeBuilderAction(string $name): self
    {
        if(array_key_exists($name, $this->builderInterfaces)) {
            unset($this->builderInterfaces[$name]);
        }
        return $this;
    }

    final public function addSubmitAction(string $name, DashboardFormSubmitInterface $submitter): self
    {
        if(!in_array($submitter, $this->submitInterfaces, true)) {
            $this->submitInterfaces[$name] = $submitter;
        }
        return $this;
    }

    final public function getSubmitAction(string $name): ?DashboardFormSubmitInterface
    {
        return $this->submitInterfaces[$name] ?? null;
    }

    final public function removeSubmitAction(string $name): self
    {
        if(array_key_exists($name, $this->submitInterfaces)) {
            unset($this->submitInterfaces[$name]);
        }
        return $this;
    }

    final public function build(): void
    {
        $this->buildForm();
        foreach($this->builderInterfaces as $builderInterface) {
            $builderInterface->onBuild($this);
        }
    }

    final public function handleSubmission(): self
    {
        if($this->isSubmitted()) {
            $resource = $this->filterResource(); // Local resolver

            foreach($this->submitInterfaces as $submitter) {
                $submitter->onFilter($resource, $this);
            }
            
            $resource = $this->validateResource($resource); // Local Resolver

            foreach($this->submitInterfaces as $submitter) {
                $submitter->onValidate($resource, $this);
            }
            
            $resource = $this->persistResource($resource); // Local Resolver
            
            foreach($this->submitInterfaces as $submitter) {
                $submitter->onPersist($resource, $this);
            }

            $this->resolveSubmission($resource); // Local Resolver
            !$this->replaceState ?: $this->implementReplaceStateJavascript();
        };
        return $this;
    }

    public function replaceHistoryState(bool $replace = true): self
    {
        $this->replaceState = !is_null($this->replaceState) && $replace;
        return $this;
    }

    public function implementReplaceStateJavascript(): void
    {
        $this->replaceState = null;
        $caller = 'window.history.replaceState';
        $href = 'window.location.href';
        $stateReplacement = sprintf('<script>%1$s && %1$s(null,null,%2$s);</script>', $caller, $href);
        BlockManager::instance()
            ->getBlock("body_javascript")
            ->addContent("history.state", $stateReplacement);
    }

    protected function filterResource(): array
    {
        return match($_SERVER['REQUEST_METHOD']) {
            'POST' => $_POST,
            'GET' => $_GET,
            default => call_user_func(function (): ?string {
                $input = file_get_contents("php://input");
                $jsonValue = json_decode($input, true);
                return json_last_error() === JSON_ERROR_NONE ? $jsonValue : ['input' => $input];
            }),
        };
    }

    protected function generateField(string $key, array $context, ?Collection $collection = null): FieldPedigree
    {
        $field = new Field($context['nodeName'] ?? Field::NODE_INPUT, $context['nodeType'] ?? Field::TYPE_TEXT);
        $fieldContext = $field->getElementContext();

        $fieldContext->setFixed($context['fixed'] ?? false);
        
        $collection ??= $this->collection;
        $collection->addField($key, $field);

        $pedigree = $this->getFieldPedigree($field);

        foreach(($context['attributes'] ?? []) as $key => $value) {
            $fieldContext->widget->setAttribute($key, $value);
        }

        if($pedigree->widget->isSelective()) {
            $pedigree->widget->setOptions($context['options'] ?? []);
        }
        
        $pedigree->widget
            ->setValue($context['value'] ?? null)
            ->setRequired($context['required'] ?? !$pedigree->widget->isHidden())
            ->setDisabled($context['disabled'] ?? false)
            ->setReadonly($context['readonly'] ?? false)
        ;

        if($pedigree->widget->isCheckable()) {
            $pedigree->widget->setChecked($context['checked'] ?? false);
            if(array_key_exists('value.alt', $context)) {
                $gadget = new Gadget(Field::NODE_INPUT, Field::TYPE_HIDDEN);
                $gadget->widget->setValue($context['value.alt']);
                $gadget->container->addClass('d-none');
                $field->addGadget($key, $gadget);
                $field->setGadgetPosition($gadget, Position::BEFORE, $pedigree->gadget);
            }
        }

        $fieldContext->frame->addClass($context['class'] ?? null);

        $fieldContext->info
            ->setValue($context['info'] ?? null)
            ->addClass($context['info-class'] ?? null);

        $fieldContext->validation
            ->setValue($context['validation'] ?? null)
            ->addClass($context['validation-class'] ?? null);

        if(!empty($context['label'])) {
            $pedigree->gadget->label->setValue($context['label']);
        }
        
        if(!empty($context['lineBreak'])) {
            $fieldContext->lineBreak
                ->setDOMHidden(false)
                ->addClass($context['lineBreak-class'] ?? 'border-bottom')
            ;
        }

        if($fieldContext->widget->isButton()) {
            $fieldContext->widget->setButtonContent($context['content'] ?? 'Submit');
        }

        if(!empty($context['suffix'])) {
            $fieldContext->suffix->setValue($context['suffix']);
        }

        if(!empty($context['prefix'])) {
            $fieldContext->prefix->setValue($context['prefix']);
        }

        return $pedigree;
    }
}
