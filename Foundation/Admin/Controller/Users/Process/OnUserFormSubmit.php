<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users\Process;

use Module\Dashboard\Bundle\Common\Password;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardForm;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormSubmitInterface;
use Module\Dashboard\Bundle\User\User;

abstract class OnUserFormSubmit implements DashboardFormSubmitInterface
{
    protected array $roles;

    public function __construct(protected User $client)
    {}

    public function onFilter(array &$resource, AbstractDashboardForm $form): void
    {
        $this->roles = array_keys(
            array_filter(
                $resource['roles'],
                fn ($value) => !empty($value)
            )
        );
        unset($resource['roles']);
    }

    public function onValidate(array &$resource, AbstractDashboardForm $form): void
    {
        $password = new Password($resource['password']);
        $resource['password'] = $password->getHash();
    }

    public function onPersist(mixed &$response, AbstractDashboardForm $form): void
    {
        if($this->client->isAvailable()) {
            $this->client->roles->set($this->roles);
        }
        $form->setProperty('history.replaceState', false);
    }
}