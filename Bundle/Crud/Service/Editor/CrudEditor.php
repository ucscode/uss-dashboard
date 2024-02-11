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
use Uss\Component\Manager\Entity;

class CrudEditor extends AbstractCrudEditor
{
    public function build(): UssElement
    {
        parent::build();
        new CrudWidgetManager($this);
        $this->getForm()->export();
        return $this->baseContainer;
    }

    public function setEntity(array|Entity $entity): self
    {
        $this->entity = is_array($entity) ? new Entity($this->tableName, $entity) : $entity;
        $this->getForm()->populate($this->entity->getAll());
        $this->getForm()
            ->getFieldPedigree(CrudEditorForm::SUBMIT_KEY)
            ?->widget->setButtonContent("Save Changes");
        return $this;
    }

    public function setEntityByOffset(string $offsetValue): bool
    {
        $entityProperties = $this->fetchEntityProperties($offsetValue);
        return $entityProperties ? !!$this->setEntity($entityProperties) : false;
    }

    public function getEntity(): Entity
    {
        return $this->entity;
    }

    public function hasEntity(): bool
    {
        return !empty($this->getEntity()->getAll());
    }

    public function isPersistable(): bool
    {
        $offset = $this->getPrimaryOffset();
        if($this->entity && !empty($offset)) {
            $value = $this->entity->get($offset);
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
        return !!$this->fetchEntityProperties();
    }

    public function persistEntity(): bool
    {
        if($this->isPersistable()) {

            $entityProperties = array_map(
                fn($value) => $value !== null ? Uss::instance()->sanitize($value, true) : $value,
                $this->filterCastedEntity()
            );

            $sQuery = new SQuery();

            try {

                if($this->isEntityInDatabase()) {

                    $objective = CrudEnum::UPDATE;

                    $SQL = $sQuery
                        ->update($this->tableName, $entityProperties)
                        ->where($this->getEntityCondition())
                        ->build();

                } else {

                    $objective = CrudEnum::CREATE;

                    $SQL = $sQuery
                        ->insert($this->tableName, $entityProperties)
                        ->build();
                }

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

    public function setEntityValue(string $columnName, ?string $value): self
    {
        $this->entity[$columnName] = $value;
        $this->getForm()->populate([$columnName => $value]);
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