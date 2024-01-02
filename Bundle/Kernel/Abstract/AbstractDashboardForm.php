<?php

namespace Module\Dashboard\Bundle\Kernel\Abstract;

use Module\Dashboard\Bundle\Common\Password;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormBuilderInterface;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormInterface;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormSubmitInterface;
use Ucscode\UssForm\Collection\Collection;
use Ucscode\UssForm\Form\Attribute;
use Ucscode\UssForm\Form\Form;

abstract class AbstractDashboardForm extends Form implements DashboardFormInterface
{
    abstract protected function buildForm(): void;
    abstract protected function resolveSubmission(mixed $response): void;

    public readonly Collection $collection;
    private array $submitInterfaces = [];
    private array $builderInterfaces = [];

    public function __construct(Attribute $attribute = new Attribute()) 
    {
        parent::__construct($attribute);
        $this->collection = $this->getCollection(self::DEFAULT_COLLECTION);
    }

    /**
     * @Override
     */
    public function filterResource(): array
    {
        return match($_SERVER['REQUEST_METHOD']) {
            'POST' => $_POST,
            'GET' => $_GET,
            default => call_user_func(function(): ?string {
                $responseInput = file_get_contents("php://input");
                $jsonValue = json_decode($responseInput, true);
                return json_last_error() === JSON_ERROR_NONE ? $jsonValue : $responseInput;
            }),
        };
    }

    final public function handleSubmission(): void
    {
        if($this->isSubmitted()) 
        {
            $filteredResource = $this->filterResource();

            array_walk(
                $this->submitInterfaces,
                fn (DashboardFormSubmitInterface $submitter) => $submitter->onSubmit($filteredResource)
            );

            $validatedResource = $this->validateResource($filteredResource);
            $justified = is_array($validatedResource) || $validatedResource === true; 
            

            if($justified) 
            {

                array_walk(
                    $this->submitInterfaces,
                    fn (DashboardFormSubmitInterface $submitter) => $submitter->onValidateResource($validatedResource)
                );
                
                $response = $this->persistResource(
                    is_array($validatedResource) ? $validatedResource : $filteredResource
                );

                array_walk(
                    $this->submitInterfaces,
                    fn (DashboardFormSubmitInterface $submitter) => $submitter->onPersistResource($response)
                );

                $this->resolveSubmission($response);
            }
        };
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
        array_walk(
            $this->builderInterfaces, 
            fn (DashboardFormBuilderInterface $exporter) => $exporter->onBuild($this)
        );
    }

    protected function getPasswordResolver(?string $password): array
    {
        $password = new Password($password);
        $strength = $password->calculateStrength();
        
        $requirements = [];
        $resolved = [];

        $factors = [
            'minLength' => $password->getMinLength() . ' characters',
            'lowerCase' => 'lowercase letter',
            'upperCase' => 'uppercase letter',
            'number' => 'number',
            'specialChar' => 'special character'
        ];

        $errorLevel = [
            [
                "required", 
                "text-danger"
            ],
            [
                "too weak", 
                "text-danger"
            ],
            [
                "weak", 
                "text-danger"
            ],
            [
                "fair", 
                "text-info"
            ],
            [
                "strong", 
                "text-success"
            ],
            [
                "very strong", 
                "text-success"
            ],
        ];

        $passwordResolver = [
            'strength' => $strength
        ];

        foreach($factors as $key => $value) 
        {
            $approved = match($key) {
                'minLength' => $password->hasMinLength(),
                'lowerCase' => $password->hasLowerCase(),
                'upperCase' => $password->hasUpperCase(),
                'number' => $password->hasNumber(),
                default => $password->hasSpecialChar(),
            };

            $approved ? 
                $resolved[$key] = $value :
                $requirements[$key] = $value;
        }

        $passwordResolver['requirements'] = $requirements;
        $passwordResolver['resolved'] = $resolved;
        $passwordResolver['errorLevel'] = $errorLevel[$strength][0];
        $passwordResolver['errorMessage'] = sprintf("Password is %s", $passwordResolver['errorLevel']);
        $passwordResolver['appearance'] = $errorLevel[$strength][1];
        $passwordResolver['instance'] = $password;

        return $passwordResolver;
    }
}
