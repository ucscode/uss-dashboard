<?php

namespace Module\Dashboard\Foundation\Admin\Form;

use Module\Dashboard\Foundation\User\Form\Entity\Security\LoginForm as UserLoginForm;
use Ucscode\UssForm\Field\Field;

class LoginForm extends UserLoginForm
{
    public function buildForm(): void
    {
        $this->createCustomField([
            'nodeType' => Field::TYPE_EMAIL,
            'name' => 'user[access]',
            'label' => "Email",
            'prefix' => '<i class="bi bi-at"></i>'
        ]);

        $this->createPasswordField();
        $this->createNonceField();
        $this->createSubmitButton();
        $this->hideLabels();
    }
}
