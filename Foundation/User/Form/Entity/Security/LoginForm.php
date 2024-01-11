<?php

namespace Module\Dashboard\Foundation\User\Form\Entity\Security;

use Module\Dashboard\Bundle\Flash\Flash;
use Module\Dashboard\Bundle\Flash\Toast\Toast;
use Module\Dashboard\Bundle\User\Interface\UserInterface;
use Module\Dashboard\Bundle\User\User;
use Module\Dashboard\Foundation\User\Form\Abstract\AbstractUserAccountForm;
use Module\Dashboard\Foundation\User\Form\Service\EmailResolver;
use Ucscode\UssElement\UssElement;
use Ucscode\UssForm\Field\Field;
use Uss\Component\Kernel\Uss;

class LoginForm extends AbstractUserAccountForm
{
    public readonly UssElement $mailerBlock;

    public function buildForm(): void
    {
        // $this->populateWithFakeUserInfo([
        //     'user[access]' => 'annette.heller@hotmail.com',
        //     'user[password]' => '5lPO$24rcC5Q',
        // ]);

        (new EmailResolver([]))->verifyAccountEmail();

        $this->createAccessField();
        $this->createPasswordField();
        $submitField = $this->createSubmitButton();
        $this->createMailerBlockBefore($submitField);
        $this->createNonceField();
        $this->hideLabels();
    }

    public function validateResource(array $filteredResource): ?array
    {
        $resource = $this->validateNonce($filteredResource);
        if($resource) {
            $user = array_map('trim', $resource['user']);
            $user['access'] = strtolower($user['access']);
            return $user;
        }
        return null;
    }

    public function persistResource(?array $validatedResource): mixed
    {
        if($validatedResource !== null) {
            $uss = Uss::instance();
            $column = strpos($validatedResource['access'], '@') !== false ? 'email' : 'username';
            $userItem = $uss->fetchItem(UserInterface::USER_TABLE, $uss->sanitize($validatedResource['access'], true), $column);
            return $this->getUserInstance($userItem, $column, $validatedResource['password']);
        }
        return null;
    }

    protected function resolveSubmission(mixed $response): void
    {
        //
    }

    /**
     * @Build
     */
    protected function createAccessField(): Field
    {
        [$field, $context] = $this->getFieldVariation();

        $context->widget
            ->setAttribute('placeholder', 'Email / Username')
            ->setAttribute('pattern', '^\s*(?:\w+|(?:[^@]+@[a-zA-Z0-9\-_]+(?:\.\w{2,})+))\s*$')
            ->setValue(
                $this->setFixture(
                    'user[access]',
                    mt_rand(0, 1) ? $this->faker?->username() : $this->faker?->email()
                )
            )
        ;

        $context->prefix->setValue("<i class='bi bi-person-check-fill'></i>");

        $this->collection->addField("user[access]", $field);

        return $field;
    }

    /**
     * @Build
     */
    protected function createMailerBlockBefore(Field $submitField): void
    {
        $this->mailerBlock = new UssElement(UssElement::NODE_DIV);

        $htmlContent = "
            <div class='resend-email ms-auto'>
                <a href='javascript:void(0)' title='Resend Confirmation Email' data-vcode>
                    <small>Reconfirm Email</small> <i class='bi bi-envelope-at'></i>
                </a>
            </div>
        ";

        $this->mailerBlock
            ->setContent($htmlContent)
            ->setAttribute('class', 'd-flex justify-content-between my-1 col-12')
            ->setAttribute('id', 'mailer-block');


        $element = $submitField->getElementContext()->frame->getElement();
        $element->getParentElement()->insertBefore(
            $this->mailerBlock,
            $element
        );
    }

    protected function getUserInstance(?array $userItem, string $type, string $password): ?User
    {
        $toast = (new Toast())->setBackground(Toast::BG_DANGER);
        $toast->setMessage("Incorrect {$type} or password");

        if($userItem) {
            $user = new User($userItem['id']);
            if($user->verifyPassword($password)) {
                $emailConfirmed = empty($user->meta->get('verify-email:code'));
                if($emailConfirmed) {
                    $user->saveToSession();
                    return $user;
                }
                $toast->setMessage("Please verify your email to proceed");
            }
        }

        Flash::instance()->addToast("login", $toast);
        return null;
    }
}
