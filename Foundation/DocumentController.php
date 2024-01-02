<?php

namespace Module\Dashboard\Foundation;

use Module\Dashboard\Bundle\Common\Document;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardForm;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormBuilderInterface;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;
use Ucscode\UssForm\Field\Field;
use Ucscode\UssForm\Resource\Facade\Position;
use Uss\Component\Route\RouteInterface;

class DocumentController implements RouteInterface
{
    public function __construct(
        protected DashboardInterface $dashboard,
        protected Document $document
    )
    {}

    public function onload(array $matches)
    {
        $controller = $this->document->getController();
        if($controller) {
            return $controller->onload($matches);
        }
        $this->dashboard->render(
            $this->document->getTemplate(),
            $this->document->getContext()
        );
    }
}