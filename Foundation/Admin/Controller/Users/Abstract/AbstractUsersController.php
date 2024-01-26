<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users\Abstract;

use Module\Dashboard\Bundle\Document\Document;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;
use Module\Dashboard\Foundation\Admin\Controller\Users\Interface\UserControllerInterface;

abstract class AbstractUsersController implements UserControllerInterface
{
    abstract protected function composeMicroApplication(): void;

    public function __construct(protected Document $document, protected DashboardInterface $dashboard)
    {
        $this->composeMicroApplication();
    }

    protected function enableDocumentMenu(string $name, bool $enabled = true): void
    {
        foreach($this->document->getMenuItems() as $offset => $menuContext) {
            $offset == $name ?
                $menuContext->setAttribute('active', $enabled) :
                $menuContext->setAttribute('active', false);
        }
    }
}