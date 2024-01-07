<?php

namespace Module\Dashboard\Foundation\User\Form\Service;

use Ucscode\UssForm\Collection\Collection;
use Uss\Component\Kernel\Uss;

class Validator
{
    public function validateUsername(Collection $collection, ?string $username): bool
    {
        if($username !== null) {
            // Username validation logic here
            if(!preg_match("/^\w{3,}$/i", trim($username))) {
                $usernameInfo = "Username should be at least 3 characters containing only letter, numbers and underscore";
                $usernameContext = $collection->getField('user[username]')?->getElementContext();
                if($usernameContext) {
                    $usernameContext->validation->setValue('* Invalid Username');
                    $usernameContext->info->setValue($usernameInfo);
                }
                return false;
            }
        }
        return true;
    }

    public function validateEmail(Collection $collection, ?string $email): bool
    {
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
        if(!$email) {
            $emailContext = $collection->getField('user[email]')?->getElementContext();
            $emailContext ? $emailContext->validation->setValue('* Invalid email address') : null;
            return false;
        }
        return true;
    }

    public function validatePassword(Collection $collection, ?string $password): bool
    {
        $passwordResolver = (new PasswordResolver())->resolve($password);

        $information = sprintf(
            "<div class='mb-1'>Your password should contain: %s</div>",
            Uss::instance()->implodeReadable($passwordResolver['requirements'])
        );

        if($passwordResolver['strength'] < 5) {
            $passwordContext = $collection->getField("user[password]")?->getElementContext();
            if($passwordContext) {
                $passwordContext->info->setValue($information);
                $passwordContext->validation
                    ->setValue('* ' . $passwordResolver['errorMessage'])
                    ->removeClass('text-danger')
                    ->addClass($passwordResolver['appearance']);
            }
            return false;
        };

        return true;
    }

    public function validateConfirmationPassword(Collection $collection, string $password, ?string $confirmPassword): bool
    {
        if($confirmPassword !== null && $password !== $confirmPassword) {
            $passwordContext = $collection->getField("user[confirmPassword]")?->getElementContext();
            $passwordContext ? $passwordContext->validation->setValue("Password does not match") : null;
            return false;
        }
        return true;
    }
}
