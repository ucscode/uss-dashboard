<?php

namespace Module\Dashboard\Foundation\User\Form;

use Module\Dashboard\Foundation\User\Form\Abstract\AbstractUserAccountForm;

class RecoveryForm extends AbstractUserAccountForm
{
    public function buildForm(): void
    {
        $this->createEmailField();
    }
}
