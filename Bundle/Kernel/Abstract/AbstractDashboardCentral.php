<?php

namespace Module\Dashboard\Bundle\Kernel\Abstract;

use Module\Dashboard\Bundle\Immutable\DashboardImmutable;
use Module\Dashboard\Bundle\Kernel\Service\Interface\AppControlInterface;
use Module\Dashboard\Bundle\Kernel\Compact\DashboardMenuFormation;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;
use Module\Dashboard\Bundle\Kernel\Interface\ThemeInterface;
use Module\Dashboard\Foundation\System\Compact\DocumentController;
use Ucscode\TreeNode\TreeNode;
use Uss\Component\Common\AppStore;
use Uss\Component\Event\Event;
use Uss\Component\Route\Route;

abstract class AbstractDashboardCentral implements DashboardInterface
{
    public readonly AppControlInterface $appControl;
    public readonly TreeNode $menu;
    public readonly TreeNode $userMenu;
    protected bool $firewallEnabled = true;
    protected array $documents = [];
    private string $themeNamespaceConvention = 'ThemeDirectory';

    public function __construct(AppControlInterface $appControl)
    {
        $this->createApp($appControl);
    }

    /**
     * Set Initial values such as base(route), theme, user permission etc
     * Note: child class should override this method but still call it
     * parent::createProject($config);
     */
    private function createApp(AppControlInterface $appControl): void
    {
        $this->appControl = $appControl;
        $this->menu = new TreeNode('Main Menu');
        $this->userMenu = new TreeNode('User Menu');
        $this->observeApplication();
        (new Event())->addListener('modules:loaded', fn () => $this->createGUI(), -1024);
    }

    private function observeApplication(): void
    {
        $appStore = AppStore::instance();
        $appStore->add('dashboard:instances', $this, $this::class);
        foreach($this->appControl->getPermissions() as $permission) {
            !is_scalar($permission) ?: $appStore->add('dashboard:permissions', $permission);
        }
    }

    /**
     * Process the GUI/Theme for the created dashboard Application
     */
    private function createGUI(): void
    {
        $this->loadTheme();

        foreach($this->getDocuments() as $document) {
            if($document->getRoute() !== null) {
                new Route(
                    $document->getRoute(),
                    new DocumentController($this, $document),
                    $document->getRequestMethods()
                );
            }
        }

        new DashboardMenuFormation($this->menu);
        new DashboardMenuFormation($this->userMenu);
    }

    private function loadTheme(): void
    {
        $themeConfig = $this->appControl->getThemeConfig();

        // Get fully qualified className
        $FQNS = $themeConfig['FQNS'] ?? sprintf(
            'Module\\Dashboard\\%s\\%s\\', 
            $this->themeNamespaceConvention, 
            $this->appControl->getThemeFolder()
        );

        // Add single backslash if not exist
        substr($FQNS, -1) === '\\' ?: $FQNS .= '\\';

        $themeFile = sprintf(DashboardImmutable::THEMES_DIR . '/%s/Theme.php', $this->appControl->getThemeFolder());

        if(is_file($themeFile)) {
            require_once $themeFile;
            $FQCN = str_replace("\\\\", "\\", $FQNS . "Theme");
            !(class_exists($FQCN) && in_array(ThemeInterface::class, class_implements($FQCN))) ?: (new $FQCN())->onload($this);
        }
    }
}
