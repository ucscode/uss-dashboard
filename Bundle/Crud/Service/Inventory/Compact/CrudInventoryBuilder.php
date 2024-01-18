<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Compact;

use Module\Dashboard\Bundle\Common\Paginator;
use Module\Dashboard\Bundle\Crud\Service\Inventory\Abstract\AbstractCrudInventory;
use Ucscode\DOMTable\DOMTable;
use Ucscode\SQuery\SQuery;
use Uss\Component\Kernel\Uss;

class CrudInventoryBuilder
{
    protected const INDICATOR = 'page';
    protected Uss $uss;
    protected DOMTable $domTable;
    protected Paginator $paginator;
    protected SQuery $sQuery;

    public function __construct(protected AbstractCrudInventory $crudInventory)
    {
        $this->initializeProperties();
        $this->configureEntities();
        $this->buildPaginator();
    }

    protected function initializeProperties(): void
    {
        $this->uss = Uss::instance();
        $this->domTable = $this->crudInventory->getDOMTable();
        $this->sQuery = $this->crudInventory->getSQuery();
    }

    protected function configureEntities(): void
    {
        if($this->crudInventory->isInlineActionEnabled()) {
            $this->domTable->setColumn(CrudInventoryMutationIterator::ACTION_KEY);
        }
        $SQL = $this->sQuery->build();

        $this->domTable->setData(
            $this->uss->mysqli->query($SQL), 
            new CrudInventoryMutationIterator($this->crudInventory)
        );

        $element = $this->domTable->build();
        $this->crudInventory->getEntitiesContainer()->appendChild($element);
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
        $url['query'][self::INDICATOR] = Paginator::NUM_PLACEHOLDER;
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