<?php

namespace Module\Dashboard\Foundation\User\Form;

use Module\Dashboard\Foundation\User\Form\Abstract\AbstractUserAccountForm;

class RegisterForm extends AbstractUserAccountForm
{
    public function buildForm(): void
    {
        $this->populateWithFakeUserInfo();
        //$this->createUsernameField();
        $this->createEmailField();
        $this->createPasswordField();
        $this->createPasswordField(true);
        $this->createAgreementCheckboxField();
        $this->createNonceField();
        $this->createSubmitButton();
        $this->hideLabels();
    }
}
