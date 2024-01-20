<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Compact;

use Module\Dashboard\Bundle\Common\Paginator;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Interface\CrudInventoryInterface;
use Ucscode\DOMTable\DOMTable;
use Ucscode\SQuery\SQuery;
use Uss\Component\Kernel\Uss;

class CrudInventoryBuilder
{
    public const PAGE_INDICATOR = 'page';
    protected Uss $uss;
    protected DOMTable $domTable;
    protected Paginator $paginator;
    protected SQuery $sQuery;
    protected bool $inlineActionsEnabled;

    public function __construct(protected CrudInventoryInterface $crudInventory)
    {
        $this->initializeProperties();
        $this->updateTableColumns();
        $this->configureAndTraverseEntities();
        $this->buildPaginator();
    }

    protected function initializeProperties(): void
    {
        $this->uss = Uss::instance();
        $this->domTable = $this->crudInventory->getDOMTable();
        $this->sQuery = $this->crudInventory->getSQuery();
        
        $this->inlineActionsEnabled = 
            !$this->crudInventory->isInlineActionDisabled() && 
            !empty($this->crudInventory->getInlineActions());
    }

    protected function updateTableColumns(): void
    {
        if($this->inlineActionsEnabled) {
            $this->domTable->setColumn(CrudInventoryMutationIterator::ACTION_KEY, "");
        }
    }

    protected function configureAndTraverseEntities(): void
    {
        $SQL = $this->sQuery->build();
        $mysqlResult = $this->uss->mysqli->query($SQL);

        $this->domTable->setData(
            $mysqlResult, 
            new CrudInventoryMutationIterator($this->crudInventory)
        );

        $tableEntities = $this->domTable->build();

        $this->crudInventory
            ->getEntitiesContainer()
            ->appendChild($tableEntities);
    }

    protected function buildPaginator(): void
    {
        $this->paginator = new Paginator(
            $this->domTable->gettotalItems(),
            $this->domTable->getItemsPerPage(),
            $this->domTable->getCurrentPage(),
            $this->generateUrlPattern()
        );

        $this->crudInventory
            ->getPaginatorContainer()
            ->appendChild($this->paginator->getElement());
    }

    protected function generateUrlPattern(): string
    {
        $url = parse_url($_SERVER['REQUEST_URI']);
        parse_str($url['query'] ?? '', $url['query']);
        $url['query'][self::PAGE_INDICATOR] = Paginator::NUM_PLACEHOLDER;
        $query = [];
        foreach($url['query'] as $key => $value) {
            $key = urlencode($key);
            $value = $value !== Paginator::NUM_PLACEHOLDER ? urlencode($value) : $value;
            $query[] = implode("=", [$key, $value]);
        };
        $query = implode("&", $query);
        return implode("?", [$url['path'], $query]);
    }
}