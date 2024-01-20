<?php

namespace Module\Dashboard\Bundle\Crud\Service\Editor;

use Module\Dashboard\Bundle\Crud\Service\Editor\Abstract\AbstractCrudEditor;
use Module\Dashboard\Bundle\Crud\Service\Editor\Compact\FieldPedigree;
use Module\Dashboard\Bundle\Crud\Service\Editor\Compact\FormManager;
use Ucscode\SQuery\SQuery;
use Ucscode\UssElement\UssElement;
use Ucscode\UssForm\Collection\Collection;
use Ucscode\UssForm\Field\Field;
use Ucscode\UssForm\Form\Form;
use Uss\Component\Kernel\Uss;

class CrudEditor extends AbstractCrudEditor
{
    public function build(): UssElement
    {
        $this->getForm()->export();
        return $this->baseContainer;
    }

    public function setEntity(array $entity): self
    {
        if(!empty($entity)) {
            $this->immutationException();
            $this->entity = $entity;
            $this->getForm()->populate($entity);
            $this->getFieldPedigree(FormManager::SUBMIT_KEY)?->widget->setButtonContent("Save Changes");
        }
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

    public function getForm(): Form
    {
        return $this->formManager->getForm();
    }

    public function configureField(string $name, array $array): ?Field
    {
        return $this->formManager->configureField($name, $array);
    }

    public function getFieldPedigree(string|Field $context): ?FieldPedigree
    {
        return $this->formManager->getFieldPedigree($context);
    }

    public function moveFieldToCollection(string|Field $field, string|Collection $collection): bool
    {
        $fieldPedigree = $this->getFieldPedigree($field);
        $field = $fieldPedigree?->field;
        if($field && $this->getForm()->hasCollection($collection)) {
            $collection instanceof Collection ? null : $collection = $this->getForm()->getCollection($collection);
            if($collection !== $fieldPedigree->collection) {
                $fieldPedigree->collection->removeField($field);
                $collection->addField($fieldPedigree->fieldName, $field);
            }
        }
        return false;
    }
}