<?php

namespace Module\Dashboard\Bundle\Crud\Service\Editor\Abstract;

abstract class AbstractCrudEditor_Level2 extends AbstractCrudEditorFoundation
{
    public function __construct(string $tableName)
    {
        parent::__construct($tableName);
        $this->createFundamentalComponents();
    }

    protected function createFundamentalComponents(): void
    {

    }
}