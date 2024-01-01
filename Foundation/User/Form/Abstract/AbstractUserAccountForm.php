<?php

namespace Module\Dashboard\Foundation\User\Form\Abstract;

use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardForm;
use Ucscode\UssForm\Field\Field;

abstract class AbstractUserAccountForm extends AbstractDashboardForm
{
    protected function createHiddenField(string $name, ?string $value = null): Field
    {
        [$field, $context] = $this->getFieldVariation(Field::NODE_INPUT, Field::TYPE_HIDDEN);

        $context->widget->setValue($value);

        $this->collection->addField($name, $field);

        return $field;
    }

    protected function createUsernameField(string $label = 'username'): Field
    {
        [$field, $context] = $this->getFieldVariation();

        $context->widget
            ->setAttribute('placeholder', $label)
            ->setAttribute('pattern', '^\s*\w+\s*$')
            ;

        $context->label
            ->setValue($label)
            ;

        $this->collection->addField("user[username]", $field);

        return $field;
    }

    protected function createPasswordField(bool $confirmPassword = false, ?string $label = null): Field
    {
        [$field, $context] = $this->getFieldVariation(Field::NODE_INPUT, Field::TYPE_PASSWORD);

        $name = !$confirmPassword ? "user[password]" : "user[confirmPassword]";
        $label = !empty($label) ? $label : (!$confirmPassword ? 'Password' : 'Confirm Password');
        
        $context->widget
            ->setAttribute('placeholder', $label)
            ->setAttribute('pattern', '^.{4,}$')
            ;
        
        $context->label
            ->setValue($label)
            ;
        
        $this->collection->addField($name, $field);

        return $field;
    }

    protected function createEmailField($label = 'Email'): Field
    {
        [$field, $context] = $this->getFieldVariation(Field::NODE_INPUT, Field::TYPE_EMAIL);

        $context->widget
            ->setAttribute("placeholder", $label)
            ;

        $context->label
            ->setValue($label)
            ;

        $this->collection->addField("user[email]", $field);

        return $field;
    }

    protected function createSubmitButton($label = 'Submit'): Field
    {
        [$field, $context] = $this->getFieldVariation(Field::NODE_BUTTON, Field::TYPE_SUBMIT);

        $context->widget
            ->setButtonContent($label)
            ->addClass("w-100")
        ;

        $this->collection->addField("submit", $field);

        return $field;
    }

    protected function createAgreementCheckboxField(?string $label = null, bool $checked = false): Field
    {
        [$field, $context] = $this->getFieldVariation(Field::NODE_INPUT, Field::TYPE_CHECKBOX);

        if(empty($label)) {
            $label = "I agree to the Terms of service Privacy policy";
        }

        $context->label
            ->setValue($label)
            ;

        $context->widget
            ->setChecked($checked)
            ->addClass('user-select-none small')
            ->setFixed(true)
            ;

        $this->collection->addField("user[agree]", $field);

        return $field;
    }
    
    protected function hideLabels(): void
    {
        foreach($this->collection->getFields() as $field) {
            $context = $field->getElementContext();
            if(!$context->widget->isCheckable()) {
                $context->label->setDOMHidden(true);
            }
        }
    }

    protected function getFieldVariation(string $nodeName = Field::NODE_INPUT, string $nodeType = Field::TYPE_TEXT): array
    {
        $field = new Field($nodeName, $nodeType);
        return [$field, $field->getElementContext()];
    }
}