<?php

use Ucscode\UssForm\UssForm;
use Ucscode\UssForm\UssFormField;

class AdminSettingsDefaultForm extends AbstractDashboardForm
{
    public function buildForm(): void
    {
        $uss = Uss::instance();

        $this->addField(
            'company[icon]',
            (new UssFormField(UssForm::NODE_INPUT, UssForm::TYPE_FILE))
                ->setWidgetAttribute('accept', 'jpg,png,webp,gif,jpeg')
                ->setRowAttribute('class', "d-none")
                ->setLabelHidden(true)
                ->setRequired(false)
                ->setWidgetAttribute('id', 'company-icon-widget')
                ->setWidgetAttribute('data-ui-preview-uploaded-image-in', '#company-icon-img')
        );

        $this->addField(
            'company[name]',
            (new UssFormField())
                ->setWidgetValue($uss->options->get('company:name'))
        );

        $this->addField(
            'company[headline]',
            (new UssFormField())
                ->setWidgetValue($uss->options->get('company:headline'))
        );

        $this->addField(
            'company[description]',
            (new UssFormField(UssForm::NODE_TEXTAREA))
                ->setWidgetValue($uss->options->get('company:description'))
                ->setWidgetAttribute('rows', 5)
        );

        $this->setSecurityHash();

    }
}