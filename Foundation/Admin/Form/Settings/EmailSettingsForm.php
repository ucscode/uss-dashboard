<?php

namespace Module\Dashboard\Foundation\Admin\Form\Settings;

use Module\Dashboard\Bundle\Flash\Flash;
use Module\Dashboard\Bundle\Flash\Toast\Toast;
use Module\Dashboard\Foundation\Admin\Form\Settings\Abstract\AbstractEmailSettingsForm;
use Uss\Component\Kernel\Uss;
use Uss\Component\Kernel\UssImmutable;

class EmailSettingsForm extends AbstractEmailSettingsForm
{
    public function buildForm(): void
    {
        $this->createEmailCollectionFields();
        $this->createSMTPCollectionFields();
        $this->createOtherFields();
    }

    protected function validateResource(array $filteredResource): ?array
    {
        if($filteredResource) {
            $key = $_SESSION[UssImmutable::SESSION_KEY];
            $validNonce = Uss::instance()->nonce($key, $filteredResource['nonce']);
            if($validNonce) {
                unset($filteredResource['nonce']);
                $context = array_filter(
                    $filteredResource, 
                    fn ($value, $key) => in_array($key, ['company', 'smtp']),
                    ARRAY_FILTER_USE_BOTH
                );
                return $context;
            }
        }
        return null;
    }

    protected function persistResource(?array $validatedResource): mixed
    {
        $toast = (new Toast())
            ->setBackground(Toast::BG_DANGER)
            ->setMessage("Email settings not updated");

        if($validatedResource) {
            $persistence = [];
            foreach($validatedResource as $namespace => $data) {
                foreach($data as $offset => $value) {
                    $key = $namespace . ':' . $offset;
                    $persistence[] = $this->uss->options->set($key, $value);
                }
            };
            if(!in_array(false, $persistence, true)) {
                $toast
                    ->setMessage("Email settings updated successfully")
                    ->setBackground(Toast::BG_SUCCESS);
                Flash::instance()->addToast($toast);
                return $validatedResource;
            }
        }

        Flash::instance()->addToast($toast);

        return null;
    }

    protected function resolveSubmission(mixed $presistedResource): void
    {
        $this->setProperty('history.replaceState', false);
    }
}