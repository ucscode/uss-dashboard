<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users\Abstract;

use Module\Dashboard\Bundle\Document\Document;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;
use Module\Dashboard\Bundle\User\User;
use Module\Dashboard\Foundation\Admin\Controller\Users\Interface\UserControllerInterface;
use Ucscode\UssForm\Form\Form;

abstract class AbstractUsersController implements UserControllerInterface
{
    protected Document $document;
    protected DashboardInterface $dashboard;

    public function __construct(array $context)
    {
        $this->document = $context['document'];
        $this->dashboard = $context['dashboard'];
    }

    protected function enableDocumentMenu(string $name, bool $enabled = true): void
    {
        foreach($this->document->getMenuItems() as $offset => $menuContext) {
            $menuContext->setAttribute('active', $offset == $name ? $enabled : false);
        }
    }

    public function getClient(): ?User
    {
        return null;
    }

    public function getForm(): ?Form
    {
        return null;
    }
}