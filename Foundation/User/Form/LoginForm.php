<?php

namespace Module\Dashboard\Foundation\User\Form;

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
        $this->populateWithFakeUserInfo([
            'user[access]' => 'twill@funk.info',
            'user[password]' => '&z25#W12_',
        ]);

        $this->resolveConfirmationEmail();

        $this->createAccessField();
        $this->createPasswordField();
        $submitField = $this->createSubmitButton();
        $this->createMailerBlockBefore($submitField);
        $this->createNonceField();
        $this->hideLabels();
    }

    public function validateResource(array $resource): ?array
    {
        $resource = $this->validateNonce($resource);
        if($resource) {
            return $resource['user'];
        }
        return null;
    }

    public function persistResource(array $resource): mixed
    {
        $uss = Uss::instance();
        $key = strpos($resource['access'], '@') !== false ? 'email' : 'username';

        $info = $uss->fetchItem(
            UserInterface::USER_TABLE,
            $uss->sanitize($resource['access'], true),
            $key
        );

        $user = $this->validateUserInfo($key, $resource['password'], $info);

        return $user ?? false;
    }

    protected function resolveSubmission(mixed $response): void
    {
        $user = $response;
        $user->saveToSession();
    }

    /**
     * @Build
     */
    protected function createAccessField(): Field
    {
        [$field, $context] = $this->getFieldVariation();

        $context->widget
            ->setAttribute('placeholder', 'Email / Username')
            ->setAttribute('pattern', '^\s*(?:\w+|(?:[^@]+@[a-zA-Z0-9\-_]+\.\w{2,}))\s*$')
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

    protected function validateUserInfo(string $type, string $password, ?array $info): ?User
    {
        if($info) {
            $user = new User($info['id']);
            $passwordValid = $user->verifyPassword($password);
            if($passwordValid) {
                return $user;
            }
        }

        $toast = (new Toast())->setBackground(Toast::BG_DANGER);
        $toast->setMessage("Incorrect {$type} or password");
        Flash::instance()->addToast("login", $toast);

        return null;
    }

    protected function resolveConfirmationEmail(): void
    {
        $emailResolver = new EmailResolver($this->getProperties());
        $emailResolver->verifyAccountEmail();
    }
}
