<?php

namespace Module\Dashboard\Bundle\Crud\Service\Editor\Abstract;

use Module\Dashboard\Bundle\Crud\Component\CrudEnum;
use Module\Dashboard\Bundle\Crud\Service\Editor\Compact\CrudEditorForm;
use mysqli_sql_exception;
use Ucscode\SQuery\SQuery;
use Ucscode\UssForm\Collection\Collection;
use Ucscode\UssForm\Field\Field;
use Uss\Component\Kernel\Uss;
use Uss\Component\Common\Entity;

// This is the repository to manage crud editor 

abstract class AbstractCrudEditor_Level3 extends AbstractCrudEditor_Level2
{
    public function getForm(): CrudEditorForm
    {
        return $this->formManager->getForm();
    }

    public function setEntityProperties(array $entityProperties): self
    {
        $this->entity->overwrite($entityProperties);
        $this->getForm()->populate($entityProperties);
        $this->getForm()
            ->getFieldPedigree(CrudEditorForm::SUBMIT_KEY)
            ?->widget->setButtonContent("Save Changes");
        return $this;
    }

    public function setEntityPropertiesByOffset(string $offsetValue): bool
    {
        $entityProperties = $this->fetchEntityProperties($offsetValue);
        return $entityProperties ? !!$this->setEntityProperties($entityProperties) : false;
    }

    public function getEntity(): Entity
    {
        return $this->entity;
    }

    public function hasEntityProperties(): bool
    {
        return !empty($this->entity->getAll());
    }

    public function isPersistable(bool $strict = false): bool
    {
        $offset = $this->getPrimaryOffset();
        if($this->hasEntityProperties() && !empty($offset)) {
            $persistable = in_array($offset, array_keys($this->tableColumns));
            $hasValue = !$strict ?: $this->entity->get($offset) !== null;
            return $persistable && $hasValue;
        }
        return false;
    }

    public function isEntityInDatabase(): bool
    {
        return !!$this->fetchEntityProperties();
    }

    public function deleteEntity(): bool
    {
        if($this->isPersistable(true)) {
            $SQL = (new SQuery())
                ->delete()
                ->from($this->tableName)
                ->where($this->getEntityCondition())
                ->build();
            
            $deleted = Uss::instance()->mysqli->query($SQL);
            !$deleted ?: $this->lastPersistenceType = CrudEnum::DELETE;
            return $deleted;
        };
        return false;
    }

    public function persistEntity(): bool
    {
        if($this->isPersistable()) {

            $entityProperties = array_map(
                fn($value) => $value !== null ? Uss::instance()->sanitize($value, true) : $value,
                $this->filterCastedEntity()
            );
            
            $objective = CrudEnum::CREATE;
            $SQL = (new SQuery())
                ->insert($this->tableName, $entityProperties)
                ->build();

            if($this->isEntityInDatabase()) {
                $objective = CrudEnum::UPDATE;
                $SQL = (new SQuery())
                    ->update($this->tableName, $entityProperties)
                    ->where($this->getEntityCondition())
                    ->build();
            }

            try {

                $persisted = Uss::instance()->mysqli->query($SQL);

                if($persisted) {
                    $this->lastPersistenceId = $objective === CrudEnum::UPDATE ? null : Uss::instance()->mysqli->insert_id;
                    $this->lastPersistenceType = $objective;
                    $this->getForm()->populate($this->entity->getAll());
                }
                
                return $persisted;

            } catch(mysqli_sql_exception $e) {
                //
            }
            
        }
        return false;
    }


    public function getLastPersistenceType(): ?CrudEnum
    {
        return $this->lastPersistenceType;
    }

    public function getLastPersistenceId(): ?int
    {
        return $this->lastPersistenceId;
    }

    public function detachField(string|Field $field, bool $hide = true): self
    {
        $this->getForm()
            ->getFieldPedigree($field)
            ?->field->getElementContext()
            ->frame->setDOMHidden($hide);
        return $this;
    }

    public function isFieldDetached(string|Field $field): bool
    {
        return !!$this->getForm()
            ->getFieldPedigree($field)
            ?->field->getElementContext()
            ->frame->isDOMHidden();
    }

    public function moveFieldToCollection(string|Field $field, string|Collection $collection): bool
    {
        $fieldPedigree = $this->getForm()->getFieldPedigree($field);
        $field = $fieldPedigree?->field;

        if($field && $this->getForm()->hasCollection($collection)) {
            $collection instanceof Collection ? null : $collection = $this->getForm()->getCollection($collection);
            if($collection !== $fieldPedigree->collection) {
                $fieldPedigree->collection->removeField($field);
                return !!$collection->addField($fieldPedigree->fieldName, $field);
            }
        }

        return false;
    }
}