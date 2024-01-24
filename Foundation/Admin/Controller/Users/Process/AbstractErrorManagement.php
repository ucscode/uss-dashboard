<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users\Process;

use Module\Dashboard\Bundle\Common\Password;
use Module\Dashboard\Bundle\Crud\Service\Editor\CrudEditor;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardForm;
use Module\Dashboard\Bundle\User\Interface\UserInterface;
use Module\Dashboard\Bundle\User\User;
use Module\Dashboard\Foundation\Admin\Controller\Users\Tool\UserControl;
use Module\Dashboard\Foundation\User\Form\Service\PasswordResolver;
use Ucscode\UssForm\Field\Field;
use Uss\Component\Kernel\Uss;

abstract class AbstractErrorManagement
{
    protected array $roles;
    protected string $avatar;
    protected ?User $parent = null;
    protected array $postContext = [];

    public function __construct(protected User $client, protected CrudEditor $crudEditor)
    {}

    protected function handlePasswordError(string $password, AbstractDashboardForm $form): ?string
    {
        $password = new Password($password);
        $resolver = (new PasswordResolver())->resolve($password->getInput());
        $info = Uss::instance()->implodeReadable($resolver['requirements']);

        if($resolver['strength'] < $resolver['strengthLimit']) {

            $form->setPersistenceEnabled(false);
            $field = $this->getFieldByPedigree('password');
            $context = $field->getElementContext();

            $context->validation->setValue('* ' . $resolver['errorMessage']);
            $context->info
                ->setValue("<div class='border-start border-3 border-warning ps-2'>Requirements: <br> {$info}</div>")
                ->addClass('fs-12px text-secondary')
            ;

            return null;
        }
        
        return $password->getHash();
    }

    protected function handleEmailError(string $email, AbstractDashboardForm $form): string
    {
        $email = trim(strtolower($email));
        $field = $this->getFieldByPedigree('email');
        $context = $field->getElementContext();

        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $form->setPersistenceEnabled(false);
            $context->validation->setValue('* Invalid email address');
            return $email;
        }

        $client = Uss::instance()->fetchItem(UserInterface::USER_TABLE, $email, 'email');

        if($client) {
            $form->setPersistenceEnabled(false);
            $context->validation->setValue('* Email already exists');
        }

        return $email;
    }

    protected function handleUsernameError(string $username, AbstractDashboardForm $form): ?string
    {
        if(!empty($username)) {
            
            $username = trim(strtolower($username));
            $field = $this->getFieldByPedigree('username');
            $context = $field->getElementContext();

            if(!preg_match('/^\w+$/i', $username)) {
                $form->setPersistenceEnabled(false);
                $context->validation->setValue('* Invalid Username');
                $context->info
                    ->setValue('Username should only contain letters, numbers and underscore')
                    ->addClass('text-muted fs-12px')
                ;
                return $username;
            }

            $client = Uss::instance()->fetchItem(UserInterface::USER_TABLE, $username, 'username');

            if($client) {
                $form->setPersistenceEnabled(false);
                $context->validation->setValue('* Username already exists');
            }

            return $username;
        }
        return null;
    }

    protected function handleParentError(string $parentCode, AbstractDashboardForm $form): ?string
    {
        if(!empty($parentCode)) {
            
            $field = $this->getFieldByPedigree('parent');
            $context = $field->getElementContext();

            $this->parent = (new User())->allocate("usercode", $parentCode);

            if($this->parent->isAvailable() && $this->parent->getId() != $this->client->getId()) {
                return $this->parent->getId();
            }

            $form->setPersistenceEnabled(false);

            if(!$this->parent->isAvailable()) {
                $context->validation->setValue('* Invalid or non-existing parent code');
            }

            if($this->parent->getId() === $this->client->getId()) {
                $context->validation->setValue('* Cannot assign a user as the parent of the same user');
            }

            return $parentCode;
        }
        return null;
    }

    protected function handlePersistionError(): void
    {
        if($this->parent && $this->parent->isAvailable()) {
            $this->crudEditor->setEntityValue('parent', $this->parent->getUsercode());
        }
        foreach($this->postContext as $key => $value) {
            if(is_scalar($value)) {
                $this->crudEditor->setEntityValue($key, $value);
            }
        }
        (new UserControl($this->crudEditor))->autoCheckRolesCheckbox($this->roles);
    }

    protected function getFieldByPedigree(string $fieldName): ?Field
    {
        return $this->crudEditor->getForm()->getFieldPedigree($fieldName)?->field;
    }
}