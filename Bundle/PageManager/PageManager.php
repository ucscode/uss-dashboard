<?php

use Ucscode\TreeNode\TreeNode;

class PageManager
{
    public const LOGIN = 'login';

    public readonly string $name;
    private ?string $route = null;
    private ?string $template = null;
    private ?string $controller = null;
    private ?DashboardFormInterface $form = null;
    private array $requestMethods = ['GET', 'POST'];
    private array $menuItems = [];
    private array $custom = [];

    public function __construct(string $pagename)
    {
        $this->name = $pagename;
    }

    /**
     * @method setRoute
     */
    public function setRoute(?string $route): self
    {
        $this->route = $route;
        return $this;
    }

    /**
     * @method getRoute
     */
    public function getRoute(): ?string
    {
        return $this->route;
    }

    /**
     * @method setTemplate
     */
    public function setTemplate(?string $template): self
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @method getTemplate
     */
    public function getTemplate(): ?string
    {
        return $this->template;
    }

    /**
     * @method setController
     */
    public function setController(?string $controller): self
    {
        $this->validateController($controller, __METHOD__);
        $this->controller = $controller;
        return $this;
    }

    /**
     * @method getController
     */
    public function getController(): ?string
    {
        return $this->controller;
    }

    /**
     * @method setForm
     */
    public function setForm(?DashboardFormInterface $form): self
    {
        $this->form = $form;
        return $this;
    }

    /**
     * @method getForm
     */
    public function getForm(): ?DashboardFormInterface
    {
        return $this->form;
    }

    /**
     * @method setRequestMethods
     */
    public function setRequestMethods(array $methods): self
    {
        $this->validateMethod($methods, __METHOD__);
        $this->requestMethods = $methods;
        return $this;
    }

    /**
     * @method getRequestMethods
     */
    public function getRequestMethods(): array
    {
        return $this->requestMethods;
    }

    /**
     * @method setCustom
     */
    public function setCustom(string $key, mixed $value): self
    {
        $this->custom[$key] = $value;
        return $this;
    }

    /**
     * @method getCustom
     */
    public function getCustom(string $key): mixed
    {
        return $this->custom[$key];
    }

    /**
     * @method addMenuItem
     */
    public function addMenuItem(string $name, array|TreeNode $menu, TreeNode $parentMenu): self
    {
        $this->validateMenuItem($menu, $parentMenu, __METHOD__);
        $menu = is_array($menu) ? new TreeNode($name, $menu) : $menu;
        $this->menuItems[$name] = [
            'item' => $menu,
            'parent' => $parentMenu,
        ];
        return $this;
    }

    /**
     * @method getMenuItem
     */
    public function getMenuItem(string $name, bool $onlyItem = false): array|TreeNode|null
    {
        $items = $this->menuItems[$name] ?? null;
        return ($items && $onlyItem) ? $items['item'] : $items;
    }

    /**
     * @method getMenuItems
     */
    public function getMenuItems(): array 
    {
        return $this->menuItems;
    }

    /**
     * @method setRoute
     */
    public function equalsCurrentRoute(): bool
    {
        $uss = Uss::instance();
        $route = $this->route;

        if (!is_null($route)) {
            $route = $uss->filterContext($route);
            $requestArray = $uss->splitUri();

            if (!empty($requestArray)) {
                array_shift($requestArray);
                $request = implode("/", $requestArray);
                $request = $uss->filterContext($request);

                return $request === $route;
            }
        }

        return false;
    }

    private function validateController(string $controller, string $caller): void
    {
        $interface = RouteInterface::class;

        if(!class_exists($controller)) {
            throw new \Exception(
                sprintf(
                    "%s Controller Error: Class '%s' does not exist and could not be loaded",
                    __CLASS__,
                    $controller
                )
            );
        } else {
            if (!in_array($interface, class_implements($controller))) {
                throw new \Exception(
                    sprintf(
                        'The class "%s" provided to %s() must implement "%s".',
                        $controller,
                        $caller,
                        $interface
                    )
                );
            };
        }
    }

    private function validateMethod(array $method, string $caller): void
    {
        $methods = [
            'GET',
            'POST',
            'PUT',
            'PATCH',
            'DELETE'
        ];

        if(is_null($method)) {
            $method = $methods[0];
        } elseif(is_string($method)) {
            $method = [$method];
        }

        $method = array_values(array_map(function ($value) {
            return strtoupper(trim($value));
        }, $method));

        foreach(array_diff($method, $methods) as $method) {
            throw new \Exception(
                sprintf(
                    "Invalid Request Method '%s' provided in argument 2 of %s('method', ...) ",
                    $method,
                    $caller
                )
            );
        };
    }

    private function validateMenuItem(array|TreeNode $menu, TreeNode $parentMenu, string $caller): void
    {
        if ($parentMenu === $menu) {
            throw new \Exception(
                sprintf(
                    '%1$s (%2$s in #argument 2) cannot be equivalent to (%2$s in #argument 3)',
                    $caller,
                    TreeNode::class
                )
            );
        }
    }
}
