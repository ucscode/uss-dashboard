<?php

namespace Module\Dashboard\Bundle\Crud\Service\Editor;

use Module\Dashboard\Bundle\Crud\Service\Editor\Abstract\AbstractCrudEditor;
use Ucscode\SQuery\SQuery;
use Uss\Component\Kernel\Uss;

class CrudEditor extends AbstractCrudEditor
{
    public function setEntity(array $entity): self
    {
        $this->immutationException();
        $this->entity = $entity;
        return $this;
    }

    public function setEntityByOffset(string $offsetValue): bool
    {
        $entity = $this->fetchEntity($offsetValue);
        return $entity ? !!$this->setEntity($entity) : false;
    }

    public function getEntity(): array
    {
        return $this->entity;
    }

    public function hasEntity(): bool
    {
        return !!$this->getEntity();
    }

    public function isPersistable(): bool
    {
        $offset = $this->getPrimaryOffset();
        if($this->entity && !empty($offset)) {
            $value = $this->entity[$offset] ?? null;
            return in_array($offset, array_keys($this->tableColumns)) && !empty($value);
        }
        return false;
    }

    public function deleteEntity(): bool
    {
        if($this->isPersistable()) {
            $SQL = (new SQuery())->delete()
                ->from($this->tableName)
                ->where($this->getEntityCondition())
                ->build();
            return Uss::instance()->mysqli->query($SQL);
        };
        return false;
    }

    public function isEntityInDatabase(): bool
    {
        return !!$this->fetchEntity();
    }

    public function persistEntity(): bool
    {
        if($this->isPersistable()) {

            $entity = array_filter(
                $this->entity, 
                fn ($value, $key) => array_key_exists($key, $this->tableColumns), 
                ARRAY_FILTER_USE_BOTH
            );

            $sQuery = new SQuery();

            $this->isEntityInDatabase() ?
                $sQuery
                    ->update($this->tableName, $entity)
                    ->where($this->getEntityCondition()) :
                $sQuery
                    ->insert($this->tableName, $entity);
            $SQL = $sQuery->build();

            return Uss::instance()->mysqli->query($SQL);
        }
        return false;
    }

    public function setEntityValue(string $columnName, ?string $value): self
    {
        $this->entity[$columnName] = $value;
        $this->mutated = true;
        return $this;
    }

    public function getEntityValue(string $columnName): ?string
    {
        return $this->entity[$columnName] ?? null;
    }

    public function removeEntityValue(string $columnName): self
    {
        if(array_key_exists($columnName, $this->entity)) {
            unset($this->entity[$columnName]);
        }
        return $this;
    }
}