<?php

namespace Module\Dashboard\Foundation\User\Form\Abstract;

use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardForm;
use Ucscode\UssForm\Field\Field;
use Uss\Component\Kernel\Uss;

abstract class AbstractUserAccountForm extends AbstractDashboardForm
{
    private ?\Faker\Generator $faker = null;
    private array $fixtures;

    private array $properties = [
        'tosLink' => null,
        'privacyLink' => null,
    ];

    public function isSubmitted(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nonce'] ?? false);
    }

    /**
     * For Testing Purpose Only
     */
    final protected function populateWithFakeUserInfo(array $fixtures = []): void
    {
        $this->fixtures = $fixtures;
        !$this->faker ? $this->faker = \Faker\Factory::create() : null;
    }

    final public function setProperty(string $name, mixed $property): self
    {
        $this->properties[$name] = $property;
        return $this;
    }

    final public function getProperty(string $name): mixed
    {
        return $this->properties[$name] ?? null;
    }

    final public function removeProperty($name): self
    {
        if(array_key_exists($name, $this->properties, true)) {
            unset($this->properties[$name]);
        }
        return $this;
    }

    protected function createHiddenField(string $name, ?string $value = null): Field
    {
        [$field, $context] = $this->getFieldVariation(Field::NODE_INPUT, Field::TYPE_HIDDEN);

        $context->widget->setValue(
            $this->setFixture(
                $name,
                $value
            )
        );

        $this->collection->addField($name, $field);

        return $field;
    }

    protected function createNonceField(string $name = 'nonce'): void
    {
        $value = Uss::instance()->nonce($_SESSION[Uss::SESSION_KEY]);
        $this->createHiddenField($name, $value);
    }

    protected function validateNonce(string|array $resource, $name = 'nonce', $unsetNonce = true): array|bool
    {
        $nonce = is_array($resource) ? ($resource[$name] ?? null) : $resource;
        if(is_string($nonce)) {
            $valid = Uss::instance()->nonce($_SESSION[Uss::SESSION_KEY], $nonce);
            if($valid) {
                if($unsetNonce && is_array($resource)) {
                    unset($resource[$name]);
                }
                return is_array($resource) ? $resource : true;
            }
        }
        return false;
    }

    protected function createUsernameField(string $label = 'username'): Field
    {
        [$field, $context] = $this->getFieldVariation();

        $name = "user[username]";

        $context->widget
            ->setAttribute('placeholder', $label)
            ->setAttribute('pattern', '^\s*\w+\s*$')
            ->setValue(
                $this->setFixture(
                    $name,
                    $this->faker->username()
                )
            )
        ;

        $context->label->setValue($label);
        $context->prefix->setValue("<i class='bi bi-person'></i>");

        $this->collection->addField($name, $field);

        return $field;
    }

    protected function createPasswordField(bool $confirmPassword = false, ?string $label = null): Field
    {
        [$field, $context] = $this->getFieldVariation(
            Field::NODE_INPUT,
            !$this->faker ? Field::TYPE_PASSWORD : Field::TYPE_TEXT
        );

        if(!$confirmPassword) {
            $name = "user[password]";
            $icon = 'lock';
        } else {
            $name = "user[confirmPassword]";
            $icon = 'shield-lock';
        };

        $label = !empty($label) ? $label : (!$confirmPassword ? 'Password' : 'Confirm Password');

        $context->widget
            ->setAttribute('placeholder', $label)
            ->setAttribute('pattern', '^.{4,}$')
            ->setValue(
                $this->setFixture(
                    $name,
                    '12345'
                )
            )
        ;

        $context->label->setValue($label);
        $context->prefix->setValue(sprintf("<i class='bi bi-%s'></i>", $icon));

        $this->collection->addField($name, $field);

        return $field;
    }

    protected function createEmailField($label = 'Email'): Field
    {
        [$field, $context] = $this->getFieldVariation(
            Field::NODE_INPUT,
            !$this->faker ? Field::TYPE_EMAIL : Field::TYPE_TEXT
        );

        $name = "user[email]";

        $context->widget
            ->setAttribute("placeholder", $label)
            ->setValue(
                $this->setFixture(
                    $name,
                    $this->faker->email()
                )
            )
        ;

        $context->label->setValue($label);
        $context->prefix->setValue("<i class='bi bi-at'></i>");

        $this->collection->addField($name, $field);

        return $field;
    }

    protected function createSubmitButton($label = 'Submit'): Field
    {
        [$field, $context] = $this->getFieldVariation(Field::NODE_BUTTON, Field::TYPE_SUBMIT);

        $context->widget
            ->setButtonContent($label)
            ->addClass("w-100")
            ->setAttribute('data-anonymous')
        ;

        $this->collection->addField("submit", $field);

        return $field;
    }

    protected function createAgreementCheckboxField(?string $label = null, bool $checked = false): Field
    {
        [$field, $context] = $this->getFieldVariation(Field::NODE_INPUT, Field::TYPE_CHECKBOX);

        $name = "user[agree]";

        if(empty($label)) {

            $void = 'javascript:void(0)';
            $tosLink = $this->getProperty('termsOfServiceUrl');
            $privacyLink = $this->getProperty('privacyPolicyUrl');

            $label = sprintf(
                "<span>
                    I agree to the 
                    <a href='%s' target='%s'>terms of service</a> &amp; 
                    <a href='%s' target='%s'>privacy policy</a>
                </spa>",
                $tosLink ?: $void,
                $tosLink ? '_blank' : '_self',
                $privacyLink ?: $void,
                $privacyLink ? '_blank' : '_self'
            );
        }

        $context->label
            ->setValue($label)
            ->addClass('user-select-none small')
        ;

        $context->widget
            ->setChecked(
                $this->setFixture(
                    $name,
                    $checked ? $checked : !!$this->faker,
                    true
                )
            )
            ->setAttribute('data-anonymous')
        ;

        $this->collection->addField($name, $field);

        return $field;
    }

    protected function createCustomField(array $info): Field
    {
        [$field, $context] = $this->getFieldVariation(
            $info['nodeName'] ?? Field::NODE_INPUT,
            $info['nodeType'] ?? Field::TYPE_TEXT
        );

        $name = $info['name'] ?? "user[]";

        $context->widget
            ->setOptions($info['options'] ?? [])
            ->setAttribute('placeholder', $info['label'] ?? null)
            ->setButtonContent(
                $info['content'] ??
                (
                    (!empty($info['value']) ? $info['value'] : null) ??
                    ucfirst(
                        $context->widget->nodeType ??
                        Field::TYPE_BUTTON
                    )
                )
            )
            ->setValue(
                $this->setFixture(
                    $name,
                    $info['value'] ?? null,
                    $context->widget->isCheckable()
                )
            )
        ;

        $context->frame
            ->addClass($info['class'] ?? null);

        $context->label
            ->setValue($info['label'] ?? null);

        $context->prefix
            ->setValue($info['prefix'] ?? null);

        $context->suffix
            ->setValue($info['suffix'] ?? null);

        $collection = $this->getCollection($info['collection'] ?? self::DEFAULT_COLLECTION);
        $collection?->addField($name, $field);

        return $field;
    }

    protected function hideLabels(): void
    {
        foreach($this->collection->getFields() as $field) {
            $context = $field->getElementContext();
            !$context->widget->isCheckable() ?
                (
                    !$context->label->isFixed() ?
                        $context->label->setDOMHidden(true) : null
                ) : null;
        }
    }

    protected function getFieldVariation(string $nodeName = Field::NODE_INPUT, string $nodeType = Field::TYPE_TEXT): array
    {
        $field = new Field($nodeName, $nodeType);
        return [$field, $field->getElementContext()];
    }

    private function setFixture(string $name, ?string $value, bool $checkable = false): ?string
    {
        $value = $this->fixtures[$name] ?? $value;
        return $this->faker ? ($checkable ? !!$value : $value) : null;
    }
}
