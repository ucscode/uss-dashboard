<?php

namespace Module\Dashboard\Bundle\Crud\Service\Editor\Compact;

use Module\Dashboard\Bundle\Crud\Component\CrudEnum;
use Module\Dashboard\Bundle\Crud\Service\Editor\Abstract\AbstractCrudEditorForm;
use Module\Dashboard\Bundle\Flash\Toast\Toast;
use Uss\Component\Kernel\Uss;

class CrudEditorForm extends AbstractCrudEditorForm
{   
    public function setPersistenceEnabled(bool $enabled): self
    {
        $this->persistenceEnabled = $enabled;
        return $this;
    }

    public function isPersistenceEnabled(): bool
    {
        return $this->persistenceEnabled;
    }

    public function getPersistenceStatus(): bool
    {
        return $this->persistenceStatus;
    }

    public function getPersistenceType(): ?CrudEnum
    {
        return $this->persistenceType;
    }

    public function getPersistenceLastInsertId(): int|string|null
    {
        return $this->persistenceLastInsertId;
    }

    public function getPersistenceError(): ?string
    {
        return $this->persistenceError;
    }

    public function isSubmitted(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST[self::NONCE_KEY]);
    }

    protected function buildForm(): void
    {}

    protected function validateResource(array $filteredResource): ?array
    {
        $nonce = $filteredResource[self::NONCE_KEY];
        unset($filteredResource[self::NONCE_KEY]);
        if(!Uss::instance()->nonce($this->nonceContext, $nonce)) {
            $toast = (new Toast())
                ->setBackground(Toast::BG_DANGER)
                ->setMessage("Invalid security token");
            $this->flash->addToast($toast, 'void-token');
            return null;
        }
        return $filteredResource;
    }

    protected function persistResource(?array $validatedResource): mixed
    {
        if($validatedResource) {

            foreach($validatedResource as $key => $value) {
                if(is_scalar($value) || is_null($value)) {
                    $value = is_bool($value) ? ($value ? 1 : 0) : $value;
                    $this->crudEditor->setEntityValue($key, $value);
                }
            }

            if($this->persistenceEnabled) {

                $this->persistenceStatus = $this->crudEditor->persistEntity();
                $this->persistenceType = $this->crudEditor->getLastPersistenceType();
                $this->persistenceError = !$this->persistenceStatus ? Uss::instance()->mysqli->error : null;
                $this->persistenceLastInsertId = 
                    $this->persistenceType === CrudEnum::CREATE ? 
                    Uss::instance()->mysqli->insert_id : null;

                $this->flash->addToast($this->getToast());
                return $this->crudEditor->getEntity(true);

            }
        }
        return null;
    }

    protected function resolveSubmission(mixed $presistedResource): void
    {}

    protected function getToast(): Toast
    {
        return (new Toast())
            ->setBackground($this->persistenceStatus ? Toast::BG_SUCCESS : Toast::BG_DANGER)
            ->setMessage($this->persistenceStatus ? 'The request was success' : 'The request failed')
        ;
    }
}