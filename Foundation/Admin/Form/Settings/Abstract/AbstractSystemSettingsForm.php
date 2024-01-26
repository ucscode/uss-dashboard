<?php

namespace Module\Dashboard\Foundation\Admin\Form\Settings\Abstract;

use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardForm;
use Ucscode\UssForm\Collection\Collection;
use Ucscode\UssForm\Field\Field;
use Uss\Component\Kernel\Uss;
use Uss\Component\Kernel\UssImmutable;

abstract class AbstractSystemSettingsForm extends AbstractDashboardForm
{
    protected Collection $avatarCollection;

    protected function createPrimaryFields(): void
    {
        $uss = Uss::instance();

        $this->createField([
            'name' => 'company[name]',
            'value' => $uss->options->get('company:name'),
        ]);

        $this->createField([
            'name' => 'company[slogan]',
            'value' => $uss->options->get('company:slogan'),
        ]);

        $this->createField([
            'nodeName' => Field::NODE_TEXTAREA,
            'name' => 'company[description]',
            'attributes' => [
                'rows' => 5,
            ],
            'required' => false,
            'value' => $uss->options->get('company:description'),
        ]);

        $this->createField([
            'name' => 'nonce',
            'nodeType' => 'hidden',
            'value' => $uss->nonce($_SESSION[UssImmutable::SESSION_KEY]),
        ]);

        $this->createField([
            'nodeType' => Field::TYPE_SUBMIT,
            'name' => 'submit',
            'attributes' => [
                'class' => 'btn btn-primary w-100',
                'data-anonymous' => null,
            ],
            'required' => false,
        ]);
    }

    protected function createAvatarFields(): void
    {
        $this->avatarCollection = new Collection();
        $this->addCollection('avatar', $this->avatarCollection);        
        
        $this->createField([
            'name' => 'logo',
            'nodeType' => Field::TYPE_FILE,
            'attributes' => [
                'id' => 'company-logo-input',
                'class' => 'd-none',
                'data-ui-preview-uploaded-image-in' => '#company-logo',
            ],
            'required' => false,
        ], $this->avatarCollection);
        
        $this->createField([
            'name' => 'logo-btn',
            'nodeType' => Field::TYPE_BUTTON,
            'attributes' => [
                'data-ui-transfer-click-event-to' => '#company-logo-input',
                'class' => 'btn btn-outline-primary btn-sm',
            ],
            'fixed' => true,
            'content' => 'Upload new logo',
        ], $this->avatarCollection);
    }

    protected function createField(array $context, ?Collection $collection = null): Field
    {
        $field = new Field(
            $context['nodeName'] ?? Field::NODE_INPUT,
            $context['nodeType'] ?? Field::TYPE_TEXT
        );
        $widgetContext = $field->getElementContext()->widget;
        foreach($context['attributes'] ?? [] as $key => $value) {
            $widgetContext->setAttribute($key, $value);
        }
        $widgetContext->setFixed($context['fixed'] ?? false);
        $widgetContext->setRequired($context['required'] ?? true);
        $widgetContext->setValue($context['value'] ?? null);
        if($widgetContext->isButton()) {
            $widgetContext->setButtonContent($context['content'] ?? 'Submit');
        }
        $collection ??= $this->collection;
        $collection->addField($context['name'], $field);
        return $field;
    }
}