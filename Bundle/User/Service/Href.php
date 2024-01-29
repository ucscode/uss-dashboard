<?php

namespace Module\Dashboard\Bundle\User\Service;

use Module\Dashboard\Bundle\Crud\Component\CrudEnum;
use Module\Dashboard\Bundle\User\User;
use Module\Dashboard\Foundation\Admin\AdminDashboard;
use Module\Dashboard\Foundation\User\UserDashboard;
use Uss\Component\Kernel\Uss;

class Href
{
    public function __construct(protected User $user)
    {}

    public function profile(): ?string
    {
        return UserDashboard::instance()->getDocument('profile')->getUrl();
    }

    public function referral(): ?string
    {
        return '';
    }

    public function editor(): ?string
    {
        $userDocument = AdminDashboard::instance()->getDocument('users');
        return Uss::instance()->replaceUrlQuery([
            'entity' => $this->user->getId(),
            'channel' => CrudEnum::UPDATE->value,
        ], $userDocument->getUrl());
    }
}