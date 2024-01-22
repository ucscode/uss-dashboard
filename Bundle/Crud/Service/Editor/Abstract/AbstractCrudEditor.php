<?php

namespace Module\Dashboard\Bundle\Crud\Service\Editor\Abstract;

use Exception;
use Ucscode\SQuery\Condition;
use Ucscode\SQuery\SQuery;
use Uss\Component\Kernel\Uss;

abstract class AbstractCrudEditor extends AbstractCrudEditor_Level2
{
    protected function getEntityCondition(?string $offsetValue = null): Condition
    {
        $offsetKey = $this->getPrimaryOffset();
        $offsetValue ??= ($this->entity ? $this->entity[$offsetKey] : '');
        return (new Condition())->add($offsetKey, $offsetValue);
    }

    protected function fetchEntity(?string $offsetValue = null): ?array
    {
        $SQL = (new SQuery())->select()
            ->from($this->tableName)
            ->where($this->getEntityCondition($offsetValue))
            ->build();
        $result = Uss::instance()->mysqli->query($SQL);
        return $result->fetch_assoc();
    }
}