<?php

namespace Module\Dashboard\Bundle\Crud\Service\Editor\Abstract;

use Module\Dashboard\Bundle\Crud\Service\Editor\Compact\FormManager;

abstract class AbstractCrudEditor_Level2 extends AbstractCrudEditorFoundation
{
    public function __construct(string $tableName)
    {
        parent::__construct($tableName);
        $this->createFundamentalComponents();
    }

    protected function createFundamentalComponents(): void
    {
        $this->formManager = new FormManager($this->tableName, $this->tableColumns);
        $this->entitiesContainer->appendChild(
            $this->formManager->getForm()->getElement()
        );
    }
}