<?php

namespace Module\Dashboard\Bundle\Crud\Service\Editor\Abstract;

use Module\Dashboard\Bundle\Crud\Component\CrudEnum;
use Module\Dashboard\Bundle\Crud\Service\Editor\Compact\FormManager;

abstract class AbstractCrudEditor_Level2 extends AbstractCrudEditorFoundation
{
    public function __construct(string $tableName)
    {
        parent::__construct($tableName);
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
            is_numeric($entityId) ? $this->setEntityByOffset($entityId) : null;
        }
    }
}