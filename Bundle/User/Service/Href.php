<?php

namespace Module\Dashboard\Bundle\User\Service;

use Module\Dashboard\Bundle\Crud\Component\CrudEnum;
use Module\Dashboard\Bundle\Document\Document;
use Module\Dashboard\Bundle\User\User;
use Module\Dashboard\Foundation\Admin\AdminDashboard;
use Module\Dashboard\Foundation\User\UserDashboard;
use Uss\Component\Kernel\Uss;

class Href
{
    public const REFERRAL_LINK_OFFSET = 'ref';

    public function __construct(protected User $user)
    {}

    public function profileLink(): ?string
    {
        return UserDashboard::instance()->getDocument('user.profile')?->getUrl();
    }

    public function referralLink(?Document $document = null): ?string
    {
        $document ??= UserDashboard::instance()->getDocument('register');
        return Uss::instance()->replaceUrlQuery([
            self::REFERRAL_LINK_OFFSET => $this->user->getUsercode(),
        ], $document->getUrl());
    }

    public function editorLink(?Document $document = null): ?string
    {
        $document ??= AdminDashboard::instance()->getDocument('users');
        return Uss::instance()->replaceUrlQuery([
            'entity' => $this->user->getId(),
            'channel' => CrudEnum::UPDATE->value,
        ], $document->getUrl());
    }
}