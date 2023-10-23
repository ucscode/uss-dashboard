<?php

use Ucscode\UssElement\UssElement;
use Ucscode\UssForm\UssForm;
use Ucscode\UssForm\UssFormInterface;

class UserRecoveryForm extends AbstractDashboardForm
{
    protected function buildForm()
    {
        $this->add('recovery[password]', UssForm::NODE_INPUT, UssForm::TYPE_PASSWORD, [

        ]);
    }

}
