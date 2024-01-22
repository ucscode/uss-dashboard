<?php

namespace Module\Dashboard\Bundle\Crud\Service\Editor\Abstract;

use Exception;
use Ucscode\SQuery\Condition;
use Ucscode\SQuery\SQuery;
use Uss\Component\Kernel\Resource\MysqliDataTypeEnum;
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

    protected function castEntity(array $entity): array
    {
        foreach($this->tableColumns as $key => $dataset) {
            $value = $entity[$key] ?? null;
            if(!is_null($value)) {
                $value = $this->refactorEntityValue(trim($value), $dataset);
                $entity[$key] = $value;
            }
        };
        return $entity;
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

    protected function refactorEntityValue(string $value, array $info): ?string
    {
        $mysqlDataTypeGroup = $this->mysqlDataTypeGroup();

        $isCharacter = in_array($info['datatype'], [
            ...$mysqlDataTypeGroup['text'],
            ...$mysqlDataTypeGroup['char'],
        ]);

        if(empty($value) && !$isCharacter && $info['nullable']) {
            return null;
        }

        $groupName = null;

        foreach($mysqlDataTypeGroup as $groupName => $datatypes) {
            if(in_array($info['datatype'], $datatypes)) {
                break;
            }
        }

        if($groupName === 'integer') {
            $value = (int)$value;
        }

        if($groupName === 'float') {
            $value = (float)$value;
        }
        
        $value = Uss::instance()->sanitize($value, true);
        
        return $value;
    }
}