<?php

namespace Module\Dashboard\Bundle\Crud\Service\Editor\Abstract;

use Module\Dashboard\Bundle\Crud\Component\CrudEnum;
use Module\Dashboard\Bundle\Crud\Service\Editor\Compact\FormManager;
use Uss\Component\Common\Entity;

# This initializes the Crud Editor properties;

abstract class AbstractCrudEditor_Level1 extends AbstractCrudEditorFoundation
{
    public function __construct(string $tableName)
    {
        parent::__construct($tableName);
        $this->entity = new Entity($this->tableName);
        $this->createFundamentalComponents();
        $this->populateEntity();
    }

    protected function createFundamentalComponents(): void
    {
        $this->formManager = new FormManager($this);
        $this->entitiesContainer->appendChild(
            $this->formManager->getForm()->getElement()
        );
    }

    protected function populateEntity(): void
    {
        if($this->getChannel() === CrudEnum::UPDATE) {
            $entityId = $_GET['entity'] ?? null;
            !is_numeric($entityId) ?: $this->setEntityPropertiesByOffset($entityId);
        }
    }
}