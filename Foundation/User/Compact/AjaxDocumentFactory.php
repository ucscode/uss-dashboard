<?php

namespace Module\Dashboard\Foundation\User\Compact;

use Module\Dashboard\Bundle\Document\Document;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboard;
use Module\Dashboard\Foundation\User\Controller\Ajax\ReconfirmRegisterEmail;

class AjaxDocumentFactory
{
    protected string $base;

    public function __construct(protected AbstractDashboard $dashboard)
    {
        $this->base = $dashboard->appControl->getUrlBasePath();
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
