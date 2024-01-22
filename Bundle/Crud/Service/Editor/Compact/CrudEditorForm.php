<?php

namespace Module\Dashboard\Bundle\Crud\Service\Editor\Compact;

use Module\Dashboard\Bundle\Crud\Service\Editor\Abstract\AbstractCrudEditorForm;
use Module\Dashboard\Bundle\Flash\Toast\Toast;
use Uss\Component\Kernel\Uss;

class CrudEditorForm extends AbstractCrudEditorForm
{   
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
            $persist = $this->crudEditor->persistEntity();
            $this->flash->addToast($this->getToast($persist));
            return $validatedResource;
        }
        return null;
    }

    protected function resolveSubmission(mixed $presistedResource): void
    {
        $this->setProperty('history.replaceState', false);
    }

    protected function getToast(bool $persist): Toast
    {
        return (new Toast())
            ->setBackground($persist ? Toast::BG_SUCCESS : Toast::BG_DANGER)
            ->setMessage($persist ? 'The request was success' : 'The request failed')
        ;
    }
}