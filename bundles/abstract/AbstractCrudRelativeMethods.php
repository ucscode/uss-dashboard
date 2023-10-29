<?php

use Ucscode\UssElement\UssElement;

abstract class AbstractCrudRelativeMethods implements CrudRelativeInterface
{
    protected string $primaryKey = 'id';
    protected bool $hideWidgets = false;
    protected array $widgets = [];

    /**
     * @method rewriteCurrentPath
     */
    public function rewriteCurrentPath(array $query = []): void
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if(!empty($query)) {
            $queryString = http_build_query($query);
            $path .= "?" . $queryString;
        };
        header("location: " . $path);
        die;
    }

    /**
     * @method setPrimaryColumn
     */
    public function setPrimaryKey(string $key): self
    {
        $this->primaryKey = $key;
        return $this;
    }

    /**
     * @method setPrimaryColumn
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    /**
     * @method setWidget
     */
    public function setWidget(string $name, UssElement $widget): self
    {
        $this->widgets[$name] = $widget;
        return $this;
    }

    /**
     * @method getWidget
     */
    public function getWidget(string $name): ?UssElement
    {
        return $this->widgets[$name] ?? null;
    }

    /**
     * @method removeWidget
     */
    public function removeWidget(string $name): self
    {
        if(array_key_exists($name, $this->widgets)) {
            unset($this->widgets[$name]);
        }
        return $this;
    }

    /**
     * @method getWidgets
     */
    public function getWidgets(): array
    {
        return $this->widgets;
    }

    /**
     * @method setHideWidgets
     */
    public function setHideWidgets(bool $status): self
    {
        $this->hideWidgets = $status && !empty($this->widgets);
        return $this;
    }

    /**
     * @method isWidgetHidden
     */
    public function isWidgetsHidden(): bool
    {
        return $this->hideWidgets;
    }
}
