<?php

namespace Module\Dashboard\Bundle\Crud\Service\Editor;

use Module\Dashboard\Bundle\Crud\Component\CrudEnum;
use Module\Dashboard\Bundle\Crud\Component\CrudWidgetManager;
use Module\Dashboard\Bundle\Crud\Service\Editor\Abstract\AbstractCrudEditor;
use Module\Dashboard\Bundle\Crud\Service\Editor\Compact\CrudEditorForm;
use mysqli_sql_exception;
use Ucscode\SQuery\SQuery;
use Ucscode\UssElement\UssElement;
use Ucscode\UssForm\Collection\Collection;
use Ucscode\UssForm\Field\Field;
use Ucscode\UssForm\Resource\Service\Pedigree\FieldPedigree;
use Uss\Component\Kernel\Uss;

class CrudEditor extends AbstractCrudEditor
{
    public function build(): UssElement
    {
        parent::build();
        new CrudWidgetManager($this);
        $this->getForm()->export();
        return $this->baseContainer;
    }

    public function setEntity(array $entity): self
    {
        $this->entity = $entity;
        $this->getForm()->populate($entity);
        $this->getForm()->getFieldPedigree(CrudEditorForm::SUBMIT_KEY)
            ?->widget->setButtonContent("Save Changes");
        return $this;
    }

    public function setEntityByOffset(string $offsetValue): bool
    {
        $entity = $this->fetchEntity($offsetValue);
        return $entity ? !!$this->setEntity($entity) : false;
    }

    public function getEntity(bool $filtered = false): array
    {
        return !$filtered ? $this->entity : array_filter($this->entity, function($value, $key) {
            return in_array($key, array_keys($this->tableColumns));
        }, ARRAY_FILTER_USE_BOTH);
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
            return in_array($offset, array_keys($this->tableColumns));
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
            
            if(Uss::instance()->mysqli->query($SQL)) {
                $this->lastPersistenceType = CrudEnum::DELETE;
                return true;
            }
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

            $this->entity = array_filter(
                $this->castEntity($this->entity), 
                fn ($value, $key) => array_key_exists($key, $this->tableColumns), 
                ARRAY_FILTER_USE_BOTH
            );
            
            $entity = array_map(function($value) {
                return $value !== null ? Uss::instance()->sanitize($value, true) : $value;
            }, $this->entity);

            $sQuery = new SQuery();

            if($this->isEntityInDatabase()) {
                $objective = CrudEnum::UPDATE;
                $sQuery
                    ->update($this->tableName, $entity)
                    ->where($this->getEntityCondition());
            } else {
                $objective = CrudEnum::CREATE;
                $sQuery->insert($this->tableName, $entity);
            }

            $SQL = $sQuery->build();

            try {
                $upsert = Uss::instance()->mysqli->query($SQL);

                if($upsert) {
                    $this->lastPersistenceType = $objective;
                    $this->getForm()->populate($this->entity);
                }
                
                return $upsert;

            } catch(mysqli_sql_exception $e) {
                //
            }
            
        }
        return false;
    }

    public function setEntityValue(string $columnName, ?string $value): self
    {
        $this->entity[$columnName] = $value;
        $this->getForm()->populate([$columnName => $value]);
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

    public function getForm(): CrudEditorForm
    {
        return $this->formManager->getForm();
    }

    public function configureField(string $name, array $array): ?FieldPedigree
    {
        return $this->formManager->configureField($name, $array);
    }

    public function moveFieldToCollection(string|Field $field, string|Collection $collection): bool
    {
        $fieldPedigree = $this->getForm()->getFieldPedigree($field);
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

    public function detachField(string|Field $field, bool $hide = true): self
    {
        $this->getForm()->getFieldPedigree($field)?->field->getElementContext()->frame->setDOMHidden($hide);
        return $this;
    }

    public function isFieldDetached(string|Field $field): bool
    {
        return !!$this->getForm()->getFieldPedigree($field)?->field->getElementContext()->frame->isDOMHidden();
    }

    public function getLastPersistenceType(): ?CrudEnum
    {
        return $this->lastPersistenceType;
    }
}