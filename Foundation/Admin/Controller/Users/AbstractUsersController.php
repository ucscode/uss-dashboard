<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users;

use Module\Dashboard\Bundle\Common\Document;
use Module\Dashboard\Bundle\Crud\Kernel\Interface\CrudKernelInterface;
use Ucscode\UssForm\Form\Form;

abstract class AbstractUsersController
{
    abstract public function getCrudKernel(): CrudKernelInterface;
    abstract public function getForm(): ?Form;
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