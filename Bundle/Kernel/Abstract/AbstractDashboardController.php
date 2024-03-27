<?php

namespace Module\Dashboard\Bundle\Kernel\Abstract;

use Module\Dashboard\Bundle\Document\Interface\DocumentInterface;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormInterface;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Uss\Component\Route\RouteInterface;

abstract class AbstractDashboardController implements RouteInterface
{
    protected DashboardInterface $dashboard;
    protected DocumentInterface $document;
    protected ?DashboardFormInterface $form;

    public function initialize(ParameterBag $container): void
    {
        $this->dashboard = $container->get('dashboard');

        $this->document = $container->get('document');

        $this->document->setContext(
            $this->document->getContext() + [
                'form' => $this->form = $this->document?->getCustom('app.form'),
            ]
        );
    }
}
