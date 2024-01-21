<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users\Abstract;

use Module\Dashboard\Bundle\Common\Document;
use Module\Dashboard\Foundation\Admin\Controller\Users\Interface\UserControllerInterface;

abstract class AbstractUsersController implements UserControllerInterface
{
    abstract protected function composeMicroApplication(): void;

    public function __construct(protected Document $document)
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