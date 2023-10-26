<?php

use Ucscode\DOMTable\DOMTableInterface;
use Ucscode\DOMTable\DOMTable;

class CrudIndexManager extends AbstractCrudIndexManager
{
    public function getDOMTable(?DOMTableInterface $domTable = null): DOMTable 
    {
        $result = $this->uss->mysqli->query($this->sQuery);

        $domTable = new DOMTable($this->tablename);
        $domTable->setMultipleColumns($this->columns);
        $domTable->setData($result);
        $domTable->setChunks($this->paginator->getItemsPerPage());
        return $domTable;
    }

    /**
     * @method getColumns
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @method getColumns
     */
    public function setColumn(string $key, ?string $context = null): self
    {
        if(is_null($context)) {
            $context = $key;
        };
        $this->columns[$key] = $key;
        return $this;
    }

    /**
     * @method removeColumns
     */
    public function removeColumn(string $key): self
    {
        if(array_key_exists($key, $this->columns)) {
            unset($this->columns[$key]);
        }
        return $this;
    }

    /**
     * @method updateQueryBuilder
     */
    public function updateQueryBuilder(callable $func): void
    {
        $this->sQuery = $func($this->sQuery);
        $this->inspectQuery();
    }
}
