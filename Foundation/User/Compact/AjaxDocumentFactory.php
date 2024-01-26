<?php

namespace Module\Dashboard\Foundation\User\Compact;

use Module\Dashboard\Bundle\Document\Document;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;
use Module\Dashboard\Foundation\User\Controller\Ajax\ReconfirmRegisterEmail;

class AjaxDocumentFactory
{
    protected string $base;

    public function __construct(protected DashboardInterface $dashboard)
    {
        $this->base = $dashboard->appControl->getBase();
    }

    public function createResendRegisterEmailDocument(): Document
    {
        return (new Document())
            ->setName('verify-email')
            ->setRoute('/ajax/verify-email', $this->base)
            ->setController(new ReconfirmRegisterEmail())
            ->setRequestMethods(['POST'])
        ;
    }
}
