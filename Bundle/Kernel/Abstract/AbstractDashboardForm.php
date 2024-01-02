<?php

namespace Module\Dashboard\Bundle\Kernel\Abstract;

use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormBuilderInterface;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormInterface;
use Ucscode\UssForm\Collection\Collection;
use Ucscode\UssForm\Form\Attribute;
use Ucscode\UssForm\Form\Form;

abstract class AbstractDashboardForm extends Form implements DashboardFormInterface
{
    abstract protected function buildForm(): void;

    public readonly Collection $collection;
    private array $builderInterfaces = [];

    public function __construct(Attribute $attribute = new Attribute()) 
    {
        parent::__construct($attribute);
        $this->collection = $this->getCollection(self::DEFAULT_COLLECTION);
    }

    final public function addBuilderAction(string $name, DashboardFormBuilderInterface $builder): self
    {
        if(!in_array($name, $this->builderInterfaces, true)) {
            $this->builderInterfaces[$name] = $builder;
        }
        return $this;
    }

    final public function removeBuilderAction(string $name): self
    {
        if(array_key_exists($name, $this->builderInterfaces)) {
            unset($this->builderInterfaces[$name]);
        }
        return $this;
    }

    final public function build(): void
    {
        $this->buildForm();
        $this->builderInterfaces = array_filter(
            $this->builderInterfaces,
            fn ($exporter) => $exporter instanceof DashboardFormBuilderInterface
        );
        array_walk(
            $this->builderInterfaces, 
            fn (DashboardFormBuilderInterface $exporter) => $exporter->onBuild($this)
        );
    }

    /**
     * Override
     */
    public function handleSubmission(): void
    {
        if($this->isSubmitted()) {
            $data = $this->filterData();
            !$this->isValid($data) ?
                $this->resolveInvalidRequest($data) :
                (
                    $this->persistEntry($data) ? 
                    $this->onEntrySuccess($data) : 
                    $this->onEntryFailure($data)
                );
        };
    }

    /**
     * @Override
     */
    public function filterData(): array
    {
        $data = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
        return $data;
    }

    /**
     * @Override
     */
    public function isValid(array $data): bool
    {
        return !empty($data);
    }

    /**
     * @Override
     */
    public function persistEntry(array $data): bool
    {
        return false;
    }

    /**
     * @Override
     */
    public function onEntryFailure(array $data): void
    {
        
    }

    /**
     * @Override
     */
    public function onEntrySuccess(array $data): void
    {
        
    }
    
    /**
     * @Override
     */
    public function resolveInvalidRequest(?array $data): void
    {
        
    }
}
