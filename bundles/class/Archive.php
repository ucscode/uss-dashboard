<?php

use Ucscode\Packages\TreeNode;

class Archive
{
    public const LOGIN = 'login';

    public readonly string $name;
    private ?string $route = null;
    private ?string $template = null;
    private ?string $controller = null;
    private ?string $form = null;
    private array $method = ['GET', 'POST'];
    private array $menuItems = [];
    private array $custom = [];

    public function __construct(string $pagename)
    {
        $this->name = $pagename;
    }

    public function setRoute(?string $route): self
    {
        $this->route = $route;
        return $this;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function setTemplate(?string $template): self
    {
        $this->template = $template;
        return $this;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setController(?string $controller): self
    {
        $this->validateController($controller, __METHOD__);
        $this->controller = $controller;
        return $this;
    }

    public function getController(): ?string
    {
        return $this->controller;
    }

    public function setForm(?string $form): self
    {
        $this->validateForm($form, __METHOD__);
        $this->form = $form;
        return $this;
    }

    public function getForm(): ?string
    {
        return $this->form;
    }

    public function setMethods(array $method): self
    {
        $this->validateMethod($method, __METHOD__);
        $this->method = $method;
        return $this;
    }

    public function getMethods(): array
    {
        return $this->method;
    }

    public function setCustom(string $key, mixed $value): self
    {
        $this->custom[$key] = $value;
        return $this;
    }

    public function getCustom(string $key): mixed
    {
        return $this->custom[$key];
    }

    public function addMenuItem(string $name, array|TreeNode $menu, TreeNode $parentMenu): self
    {
        if ($parentMenu === $menu) {
            throw new \Exception(
                sprintf(
                    '%1$s (%2$s in #argument 2) cannot be equivalent to (%2$s in #argument 3)',
                    __METHOD__,
                    TreeNode::class
                )
            );
        }

        if (is_array($menu)) {
            $menu = new TreeNode($name, $menu);
        }

        $this->menuItems[$name] = [
            'item' => $menu,
            'parent' => $parentMenu,
        ];

        return $this;
    }

    public function getMenuItem(?string $name = null, bool $returnItem = false): array|TreeNode|null
    {
        if (is_null($name)) {
            return $this->menuItems;
        }
        $items = $this->menuItems[$name] ?? null;
        if ($items && $returnItem) {
            return $items['item'];
        }
        return $items;
    }

    public function equalsCurrentRoute(): bool
    {
        $uss = Uss::instance();
        $route = $this->route;

        if (!is_null($route)) {
            $route = $uss->filterContext();
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

        $method = array_values(array_map(function ($val) {
            return strtoupper(trim($val));
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

    private function validateForm(string $form, string $caller): void
    {
        $interface = DashboardFormInterface::class;
        if(!in_array($interface, class_implements($form))) {
            throw new \Exception(
                sprintf(
                    'The class "%s" provided to %s() must implement "%s".',
                    $form,
                    $caller,
                    $interface
                )
            );
        }
    }
}
