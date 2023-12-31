<?php

namespace Module\Dashboard\Bundle\Kernel\Abstract;

use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormInterface;
use Ucscode\UssForm\Form\Attribute;
use Ucscode\UssForm\Form\Form;

abstract class AbstractDashboardForm extends Form implements DashboardFormInterface
{
    abstract protected function buildForm();

    public function __construct(Attribute $attribute = new Attribute()) 
    {
        parent::__construct($attribute);
    }

    /**
     * Override
     */
    public function handleSubmission(): void
    {
        if($this->isSubmitted()) {
            !$this->isTrusted() ?
                $this->resolveUntrustedRequest() : call_user_func(function() {
                    $data = $this->filterData();
                    !$this->isValid($data) ?
                        $this->resolveInvalidRequest($data) :
                        (
                            $this->persistEntry($data) ? 
                            $this->onEntrySuccess($data) : 
                            $this->onEntryFailure($data)
                        );
                });

        };
    }

    /**
     * @Override
     */
    public function isSubmitted(): bool
    {
        return false;
    }

    /**
     * @Override
     */
    public function isTrusted(): bool
    {
        return false;
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
    public function resolveUntrustedRequest(): void
    {
        
    }

    /**
     * @Override
     */
    public function resolveInvalidRequest(?array $data): void
    {
        
    }
}
