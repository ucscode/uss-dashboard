<?php

use Ucscode\Packages\TreeNode;

class Archive
{
    public const LOGIN = 'login';

    public readonly string $name;

    private array $attributes = [
        'route' => null,
        'template' => null,
        'controller' => null,
        'form' => null,
        'method' => ['GET', 'POST'],
    ];

    private array $menuItems = [];

    private array $custom = [];

    public function __construct(string $pagename)
    {
        $this->name = $pagename;
    }

    /**
     * Set a new System Defined Attribute
     */
    public function set(string $key, string|array|null $value): self
    {
        $key = $this->validate($key, $value);
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Get a System Defined Attribute
     */
    public function get(string $key): string|array|null
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Set a new Custom Attribute
     */
    public function setCustom(string $key, mixed $value): self
    {
        $this->custom[$key] = $value;
        return $this;
    }

    /**
     * Get a Custom Attribute
     */
    public function getCustom(string $key): mixed
    {
        return $this->custom[$key];
    }

    /**
     * Add a menu Item:
     * The parent of the menu item must be specified
     */
    public function addMenuItem(string $name, array|TreeNode $menu, TreeNode $parentMenu): self
    {
        // Validate Menu Item
        if($parentMenu === $menu) {
            throw new \Exception(
                sprintf(
                    '%1$s (%2$s in #argument 2) cannot be equivalent to (%2$s in #argument 3)',
                    __METHOD__,
                    TreeNode::class
                )
            );
        };

        if(is_array($menu)) {
            $menu = new TreeNode($name, $menu);
        }

        $this->menuItems[$name] = [
            'item' => $menu,
            'parent' => $parentMenu
        ];

        return $this;
    }

    /**
     * Get Menu Items
     */
    public function getMenuItem(?string $name = null, bool $returnItem = false): array|TreeNode|null
    {
        if(is_null($name)) {
            return $this->menuItems;
        };
        $items = $this->menuItems[$name] ?? null;
        if($items && $returnItem) {
            return $items['item'];
        };
        return $items;
    }

    /**
     * Check if Page matches Current Route
     */
    public function equalsCurrentRoute(): bool
    {
        $uss = Uss::instance();
        $route = $this->attributes['route'];

        if(!is_null($route)) {

            $route = $uss->filterContext();
            $requestArray = $uss->splitUri();

            if(!empty($requestArray)) {

                array_shift($requestArray);
                $request = implode("/", $requestArray);
                $request = $uss->filterContext($request);

                return $request === $route;

            };

        }

        return false;
    }

    private function validate(string $key, string|array|null $value): string
    {
        $key = strtolower($key);
        if(!array_key_exists($key, $this->attributes)) {
            $this->requirementException($key);
        } elseif($key === 'controller') {
            $this->controllerException($value);
        } elseif($key === 'method') {
            $this->methodException($value);
        } elseif($key === 'form') {
            $this->formException($value);
        }
        return $key;
    }

    private function requirementException(string $key)
    {
        throw new \Exception(
            sprintf(
                'The attribute "%s" does not exist, use "%s::%s()" to define custom attributes instead',
                $key,
                __CLASS__,
                'setCustom'
            )
        );
    }

    private function controllerException($value): void
    {
        $interface = RouteInterface::class;

        if(empty($value) || !is_string($value)) {
            throw new \Exception(
                sprintf(
                    "%s Controller Error: Controller value must be a Fully Qualified Class Name that implements %s",
                    __CLASS__,
                    $interface
                )
            );
        } elseif(!class_exists($value)) {
            throw new \Exception(
                sprintf(
                    "%s Controller Error: Class '%s' does not exist and could not be loaded",
                    __CLASS__,
                    $value
                )
            );
        } else {
            if (!in_array($interface, class_implements($value))) {
                throw new \Exception(
                    sprintf(
                        'The class "%s" provided to %s::%s("controller", ...) must implement "%s".',
                        $value,
                        __CLASS__,
                        'set',
                        $interface
                    )
                );
            };
        }
    }

    private function methodException($value): void
    {
        $methods = [
            'GET',
            'POST',
            'PUT',
            'PATCH',
            'DELETE'
        ];

        if(is_null($value)) {
            $value = $methods[0];
        } elseif(is_string($value)) {
            $value = [$value];
        }

        $value = array_values(array_map(function ($val) {
            return strtoupper(trim($val));
        }, $value));

        foreach(array_diff($value, $methods) as $method) {
            throw new \Exception(
                sprintf(
                    "Invalid Request Method '%s' provided in argument 2 of %s::%s('method', ...) ",
                    $method,
                    __CLASS__,
                    'set'
                )
            );
        };
    }

    private function formException($value): void
    {
        $interface = DashboardFormInterface::class;
        if(!in_array($interface, class_implements($value))) {
            throw new \Exception(
                sprintf(
                    'The class "%s" provided to %s::%s("form", ...) must implement "%s".',
                    $value,
                    __CLASS__,
                    'set',
                    $interface
                )
            );
        }
    }

}
