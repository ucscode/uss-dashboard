<?php

namespace Module\Dashboard\Foundation\Admin\Form\Settings\Abstract;

use Module\Dashboard\Bundle\Common\AppStore;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardForm;
use Ucscode\UssForm\Field\Field;
use Ucscode\UssForm\Resource\Service\Pedigree\FieldPedigree;
use Uss\Component\Kernel\Uss;
use Uss\Component\Kernel\UssImmutable;

abstract class AbstractUsersSettingsForm extends AbstractDashboardForm
{
    public const RULER_CLASS = 'border-bottom pb-3 mb-3';
    protected Uss $uss;

    public function __construct()
    {
        parent::__construct();
        $this->uss = Uss::instance();
    }

    protected function createSignupDisabledField(): void
    {
        $this->createUserOptionField("user:disable-signup", [
            'label' => "Temporarily disable signup",
            'info' => "For the time being, prevent registration",
        ]);
    }

    protected function createCollectUsernameField(): void
    {
        $this->createUserOptionField("user:collect-username", [
            'label' => "Collect username at signup",
            'info' => "Hide or display the username input in the registration form",
        ]);
    }

    protected function createConfirmEmailField(): void
    {
        $this->createUserOptionField("user:confirm-email", [
            'label' => "Send confirmation email after registration",
            'info' => "Enforce user to confirm their email before they can login",
        ]);
    }

    protected function createReadonlyEmailField(): void
    {
        $this->createUserOptionField("user:lock-email", [
            'label' => "Lock user email",
            'info' => "Prevent user from changing their email after login",
        ]);
    }

    protected function createReconfirmEmailField(): void
    {
        $this->createUserOptionField("user:reconfirm-email", [
            'label' => "Resend confirmation email after update",
            'info' => "Enforce user to confirm their new email address",
        ]);
    }

    protected function createAccountAutoDeletionField(): void
    {
        $this->generateField('user[remove-inactive-after-day]', [
            'label' => 'Delete Inactive User',
            'info' => 'Automatically delete unconfirmed account after several days. <div class="text-secondary small">&gt;&gt; Leave at zero to disable</div>',
            'suffix' => 'days',
            'nodeType' => Field::TYPE_NUMBER,
            'class' => self::RULER_CLASS,
            'value' => $this->uss->options->get('user:remove-inactive-after-day'),
        ]);
    }

    protected function createDefaultRoleField(): void
    {
        $permissions = AppStore::instance()->get('app:permissions');
        sort($permissions);
        $this->generateField('user[default-role]', [
            'label' => 'Default Registration Role',
            'nodeName' => Field::NODE_SELECT,
            'class' => self::RULER_CLASS,
            'options' => array_combine($permissions, $permissions),
            'value' => $this->uss->options->get('user:default-role'),
            'info' => 'Initial user registration roles to determine their default permissions and access levels',
        ]);
    }

    protected function createNonceField(): void
    {
        $nonce = $this->uss->nonce($_SESSION[UssImmutable::SESSION_KEY]);
        
        $this->generateField('nonce', [
            'nodeType' => Field::TYPE_HIDDEN,
            'value' => $nonce,
        ]);
    }

    protected function createSubmitButton(): void
    {
        $this->generateField('submit', [
            'fixed' => true,
            'widget-attributes' => [
                'data-anonymous' => '',
                'class' => 'btn btn-primary w-100',
            ],
            'nodeType' => Field::TYPE_SUBMIT,
            'content' => 'Save Changes',
        ]);
    }

    protected function createUserOptionField(string $key, array $options): FieldPedigree
    {
        $split = explode(":", $key);
        $name = sprintf("%s[%s]", $split[0], $split[1]);

        return $this->generateField($name, $options + [
            'nodeType' => Field::TYPE_SWITCH,
            'required' => false,
            'value.alt' => 0,
            'value' => 1,
            'class' => self::RULER_CLASS,
            'checked' => !empty($this->uss->options->get($key)),
        ]);
    }
}