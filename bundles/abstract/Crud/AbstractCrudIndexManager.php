<?php

use Ucscode\DOMTable\DOMTable;
use Ucscode\SQuery\SQuery;
use Ucscode\UssElement\UssElement;
use Ucscode\UssForm\UssForm;

abstract class AbstractCrudIndexManager extends AbstractCrudRelativeMethods implements CrudIndexInterface, CrudActionImmutableInterface
{
    protected array $tableColumns;

    protected bool $displayTfoot = false;
    protected bool $hideItemActions = false;
    protected bool $hideBulkActions = false;
    protected bool $displayItemActionsAsButton = false; // displays as dropdown
    protected bool $tableWhiteBackground = false;

    protected array $bulkActions = [];
    protected array $itemActions = [];

    protected DOMTable $domTable;
    protected Paginator $paginator;

    protected UssElement $mainBlock;
    protected UssElement $widgetBlock;
    protected UssElement $paginatorBlock;
    protected UssElement $tableBlock;
    protected UssForm $tableForm;

    protected SQuery $sQuery;
    protected mysqli_result $mysqliResult;

    /**
     * @method getTotalItems
     */
    public function getTotalItems(): int
    {
        return $this->domTable->getTotalItems();
    }

    /**
     * @method setItemsPerPage
     */
    public function setItemsPerPage(int $index): CrudIndexInterface
    {
        $this->domTable->setItemsPerPage($index);
        return $this;
    }

    /**
     * @method getItemsPerPage
     */
    public function getItemsPerPage(): int
    {
        return $this->domTable->getItemsPerPage();
    }

    /**
     * @method setCurrentPage
     */
    public function setCurrentPage(int $page): CrudIndexInterface
    {
        $this->domTable->setCurrentPage($page);
        return $this;
    }

    /**
     * @method getCurrentPage
     */
    public function getCurrentPage(): int
    {
        return $this->domTable->getCurrentPage();
    }

    /**
     * @method setMultipleTableColumns
     */
    public function setMultipleTableColumns(array $columns): CrudIndexInterface
    {
        $this->tableColumns = $columns;
        return $this;
    }

    /**
     * @method getTableColumns
     */
    public function getTableColumns(): array
    {
        return $this->tableColumns;
    }

    /**
     * @method setTableColumn
     */
    public function setTableColumn(string $column, ?string $display = null): CrudIndexInterface
    {
        if(is_null($display)) {
            $display = $column;
        };
        $this->tableColumns[$column] = $display;
        return $this;
    }

    /**
     * @method removeTableColumn
     */
    public function removeTableColumn(string $column): CrudIndexInterface
    {
        if(array_key_exists($column, $this->tableColumns)) {
            unset($this->tableColumns[$column]);
        };
        return $this;
    }

    /**
     * @method setDisplayTableFooter
     */
    public function setDisplayTableFooter(bool $status): CrudIndexInterface
    {
        $this->displayTfoot = $status;
        return $this;
    }

    /**
     * @method getDisplayTableFooter
     */
    public function getDisplayTableFooter(): bool
    {
        return $this->displayTfoot;
    }

    /**
     * @method addBulkAction
     */
    public function addBulkAction(string $name, CrudAction $info): CrudIndexInterface
    {
        $this->bulkActions[$name] = $info;
        return $this;
    }

    /**
     * @method
     */
    public function removeBulkAction(string $name): CrudIndexInterface
    {
        if(array_key_exists($name, $this->bulkActions)) {
            unset($this->bulkActions[$name]);
        }
        $this->hideBulkActions = empty($this->bulkActions);
        return $this;
    }

    /**
     * @method getBulkAction
     */
    public function getBulkActions(?string $name = null): CrudAction|array|null
    {
        if(is_null($name)) {
            return $this->bulkActions;
        }
        return $this->bulkActions[$name] ?? null;
    }

    /**
     * @method hideBulkActions
     */
    public function setHideBulkActions(bool $status): CrudIndexInterface
    {
        $this->hideBulkActions = $status && !empty($this->bulkActions);
        return $this;
    }

    /**
     * @method isBulkActionsHidden
     */
    public function isBulkActionsHidden(): bool
    {
        return $this->hideBulkActions;
    }

    /**
     * @method addItemAction
     */
    public function addItemAction(string $name, CrudActionInterface $action): CrudIndexInterface
    {
        $this->itemActions[$name] = $action;
        return $this;
    }

    /**
     * @method getItemAction
     */
    public function getItemActions(?string $name = null): CrudActionInterface|array|null
    {
        if(is_null($name)) {
            return $this->itemActions;
        }
        return $this->itemActions[$name] ?? null;
    }

    /**
     * @method removeItemAction
     */
    public function removeItemAction(string $name): CrudIndexInterface
    {
        if(array_key_exists($name, $this->itemActions)) {
            unset($this->itemActions[$name]);
        };
        $this->hideItemActions = empty($this->itemActions);
        return $this;
    }

    /**
     * @method hideItemAction
     */
    public function setHideItemActions(bool $status): CrudIndexInterface
    {
        $this->hideItemActions = $status && !empty($this->itemActions);
        return $this;
    }

    /**
     * @method isItemActionsHidden
     */
    public function isItemActionsHidden(): bool
    {
        return $this->hideItemActions;
    }

    /**
     * @method displayItemActionsAsButton
     */
    public function setDisplayItemActionsAsButton(bool $status): CrudIndexInterface
    {
        $this->displayItemActionsAsButton = $status;
        return $this;
    }

    /**
     * @method displayItemActionsAsButton
     */
    public function isDisplayItemActionsAsButton(): bool
    {
        return $this->displayItemActionsAsButton;
    }

    /**
     * @method setTableWhiteBackground
     */
    public function setTableWhiteBackground(bool $status = true): CrudIndexInterface
    {
        $this->tableWhiteBackground = $status;
        return $this;
    }

    /**
     * @method isTableWhiteBackground
     */
    public function isTableWhiteBackground(): bool
    {
        return $this->tableWhiteBackground;
    }

    /**
     * @method updateSQuery
     */
    public function updateSQuery(callable $updater): void
    {
        $sQuery = call_user_func($updater, $this->sQuery);
        if(!($sQuery instanceof SQuery)) {
            throw new \Exception(
                sprintf(
                    '%s must return an instance of %s; %s returned instead',
                    __METHOD__,
                    SQuery::class,
                    gettype($sQuery)
                )
            );
        };
        $this->sQuery = $sQuery;
        $this->mysqliResult = Uss::instance()->mysqli->query($this->sQuery);
    }

    /**
     * @method getUrlPattern
     */
    protected function getUrlPattern(): string
    {
        $url = parse_url($_SERVER['REQUEST_URI']);
        parse_str($url['query'] ?? '', $query);
        $url['query'] = [];
        $query[self::PAGE_INDEX_KEY] = Paginator::NUM_PLACEHOLDER;
        foreach($query as $key => $value) {
            $url['query'][] = $key . '=' . ($key === self::PAGE_INDEX_KEY ? $value : urlencode($value));
        }
        $url['query'] = implode('&', $url['query']);
        return $url['path'] . "?" . $url['query'];
    }
}
