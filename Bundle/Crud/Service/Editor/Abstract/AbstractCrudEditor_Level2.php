<?php

namespace Module\Dashboard\Bundle\Crud\Service\Editor\Abstract;

use Ucscode\SQuery\Condition;
use Ucscode\SQuery\SQuery;
use Uss\Component\Kernel\Resource\MysqliDataTypeEnum;
use Uss\Component\Kernel\Uss;

// This contain helper for crud editor logics

abstract class AbstractCrudEditor_Level2 extends AbstractCrudEditor_Level1
{
    protected function getEntityCondition(?string $value = null): Condition
    {
        $key = $this->getPrimaryOffset();
        $value ??= ($this->hasEntityProperties() ? ($this->entity->get($key) ?? '') : '');
        return (new Condition())->add($key, $value);
    }

    protected function fetchEntityProperties(?string $value = null): ?array
    {
        $SQL = (new SQuery())->select()
            ->from($this->tableName)
            ->where($this->getEntityCondition($value))
            ->build();
        $result = Uss::instance()->mysqli->query($SQL);
        return $result->fetch_assoc();
    }

    protected function filterCastedEntity(): array
    {
        $entityProperties = $this->getEntity()->getAll();
        $castedEntity = [];
        foreach($this->tableColumns as $key => $dataset) {
            $value = $entityProperties[$key] ?? null;
            $castedEntity[$key] = is_null($value) ? $dataset['COLUMN_DEFAULT'] : $this->refactorEntityValue($key, $value, $dataset);
        };
        return $castedEntity;
    }

    protected function mysqlDataTypeGroup(): array
    {
        $mysqlDataTypes = MysqliDataTypeEnum::cases();

        $groups = [
            'text' => array_filter($mysqlDataTypes, fn ($enum) => stripos($enum->value, 'TEXT') !== false),
            'integer' => array_filter($mysqlDataTypes, fn ($enum) => stripos($enum->value, 'INT') !== false),
            'char' => array_filter($mysqlDataTypes, fn ($enum) => stripos($enum->value, 'CHAR') !== false),
            'datetime' => array_filter($mysqlDataTypes, function($enum) {
                return stripos($enum->value, 'DATE') !== false || stripos($enum->value, 'TIME') !== false;
            }),
        ];

        array_walk_recursive($groups, fn (&$enum) => $enum = $enum->value);
        
        $groups['float'] = ['FLOAT', 'DOUBLE', 'DECIMAL'];

        return $groups;
    }

    protected function refactorEntityValue(string $key, ?string $value, array $info): ?string
    {
        $mysqlDataTypeGroup = $this->mysqlDataTypeGroup();

        if(empty($value) && $info['IS_NULLABLE']) {
            return null;
        }

        $groupName = null;

        foreach($mysqlDataTypeGroup as $groupName => $datatypes) {
            if(in_array($info['DATA_TYPE'], $datatypes)) {
                break;
            }
        }

        if($groupName === 'integer') {
            $value = (int)$value;
        }

        if($groupName === 'float') {
            $value = (float)$value;
        }
        
        return $value;
    }
}