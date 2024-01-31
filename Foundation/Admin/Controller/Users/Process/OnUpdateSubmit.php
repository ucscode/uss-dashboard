<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users\Process;

use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardForm;
use Module\Dashboard\Bundle\User\User;

class OnUpdateSubmit extends AbstractUserFormSubmit
{
    public function onValidate(?array &$resource, AbstractDashboardForm $form): void
    {
        if($resource) {
            
            if(empty($resource['password'])) {
                unset($resource['password']);
            }
            
            if(empty($resource['parent'])) {
                unset($resource['parent']);
            }

            parent::onValidate($resource, $form);
        }
    }

    public function onPersist(mixed &$response, AbstractDashboardForm $form): void
    {
        if(!empty($this->postContext['password'])) {
            $this->client = (new User($response['id']))->saveToSession();
        }
    }
}