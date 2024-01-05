<?php

namespace Module\Dashboard\Bundle\Kernel\Abstract;

use Module\Dashboard\Bundle\Common\Document;
use Module\Dashboard\Bundle\Immutable\DashboardImmutable;
use Module\Dashboard\Bundle\Kernel\DashboardRenderLogic;
use Module\Dashboard\Bundle\User\User;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;
use Module\Dashboard\Bundle\Kernel\Service\AppControl;
use Uss\Component\Manager\UrlGenerator;
use Uss\Component\Event\Event;
use Uss\Component\Kernel\Uss;
use Uss\Component\Kernel\Enumerator;

abstract class AbstractDashboard extends AbstractDashboardCentral
{
    public function __construct(AppControl $appControl)
    {
        parent::__construct($appControl);
    }

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
        $user->acquireFromSession();
        return $user->isAvailable() ? $user : null;
    }

    /**
     * @method themeFile
     */
    public function getTheme(string $path, Enumerator $enum = Enumerator::THEME): string
    {
        $uss = Uss::instance();
        $path = $uss->filterContext($path);

        switch($enum) {
            case Enumerator::FILE_SYSTEM:
                $prefix = DashboardImmutable::THEMES_DIR;
                break;
            case Enumerator::URL:
                $prefix = $uss->pathToUrl(DashboardImmutable::THEMES_DIR);
                break;
            default:
                $prefix = '@Theme';
        }

        $theme = $prefix . "/" . $this->appControl->getThemeFolder();
        $theme .= !empty($path) ? "/{$path}" : null;
        return $theme;
    }
}
