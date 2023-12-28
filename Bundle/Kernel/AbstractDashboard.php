<?php

namespace Module\Dashboard\Bundle\Kernel;

use Module\Dashboard\Bundle\Common\Document;
use Module\Dashboard\Bundle\User\User;
use Uss\Component\Manager\UrlGenerator;
use Uss\Component\Event\Event;
use Uss\Component\Kernel\Uss;

abstract class AbstractDashboard extends AbstractDashboardCentral
{
    /**
     * Add a new document to the dashboard application
     */
    public function addDocument(string $name, Document $document): DashboardInterface
    {
        $this->documents[$name] = $document;
        return $this;
    }

    /**
     * Get a document within the dashboard application
     */
    public function getDocument(string $name): ?Document
    {
        return $this->documents[$name] ?? null;
    }

    /**
     * Remove a document from the dashboard application
     */
    public function removeDocument(string $name): DashboardInterface
    {
        if(!empty($this->documents[$name])) {
            unset($this->documents[$name]);
        }
        return $this;
    }

    /**
     * Retrieve a list of all added documents
     */
    public function getDocuments(): array
    {
        return $this->documents;
    }

    /**
     * @method urlGenerator
     */
    public function urlGenerator(string $path = '/', array $query = []): UrlGenerator
    {
        return new UrlGenerator($path, $query, $this->appControl->getBase());
    }

    /**
     * @method enableFirewall
     */
    public function enableFirewall(bool $enable = true): self
    {
        $this->firewallEnabled = $enable;
        return $this;
    }

    /**
     * @method firewallEnabled
     */
    public function isFirewallEnabled(): bool
    {
        return $this->firewallEnabled;
    }

    /**
     * Override this method and change the logic class if you are
     * not satisified with the system built-in logic
     * @method render
     */
    public function render(string $template, array $options = []): void
    {
        (new Event())->addListener(
            'dashboard:render',
            new DashboardRenderLogic($this, $template, $options)
        );
    }

    /**
     * @method getCurrentUser
     */
    public function getCurrentUser(): ?User
    {
        $user = new User();
        $user->getFromSession();
        return $user->exists() ? $user : null;
    }
}
