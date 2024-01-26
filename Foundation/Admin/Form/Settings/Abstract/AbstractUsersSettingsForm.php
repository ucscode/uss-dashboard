<?php

namespace Module\Dashboard\Foundation\Admin\Form\Settings\Abstract;

use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardForm;
use Ucscode\UssForm\Field\Field;
use Ucscode\UssForm\Resource\Service\Pedigree\FieldPedigree;
use Uss\Component\Kernel\Uss;

abstract class AbstractUsersSettingsForm extends AbstractDashboardForm
{
    protected Uss $uss;
    protected string $rulerClass = 'border-bottom pb-3 mb-3';

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

    protected function createUserOptionField(string $key, array $options): FieldPedigree
    {
        $split = explode(":", $key);
        $name = sprintf("%s[%s]", $split[0], $split[1]);

        return $this->generateField($name, $options + [
            'nodeType' => Field::TYPE_SWITCH,
            'required' => false,
            'value.alt' => 0,
            'value' => 1,
            'class' => $this->rulerClass,
            'checked' => !empty($this->uss->options->get($key)),
        ]);
    }
}