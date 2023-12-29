<?php

use Ucscode\UssForm\UssForm;
use Ucscode\UssForm\UssFormField;

class AdminSettingsDefaultForm extends AbstractDashboardForm
{
    protected array $message = [];

    protected function init(): void 
    {
        $this->handleSubmission();
    }

    /**
     * @method buildForm
     */
    public function buildForm(): void
    {
        $uss = Uss::instance();

        $this->addField(
            'company_icon',
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

    /**
     * @method persistEntry
     */
    public function persistEntry(array $data): bool
    {
        $status = [];
        foreach($data['company'] as $name => $value) {
            $key = "company:{$name}";
            $updated = Uss::instance()->options->set($key, $value);
            // if($updated) {
            //     $this->getField("company[$name]")
            //         ->setWidgetValue($value);
            // };
            $status[] = $updated;
        }
        return !in_array(false, $status, true);
    }

    /**
     * @method onEntrySuccess
     */
    public function onEntrySuccess(array $data): void
    {
        $uss = Uss::instance();
        
        $this->message[] = "<i class='bi bi-check-circle text-success me-1'></i> Settings was successfully updated";

        $fileUploader = $this->getFileUploader();

        if(!$fileUploader->uploadFile()) {
            if($fileUploader->getError() !== 4) {
                $this->message[] = "<p class='text-danger'> 
                    <i class='bi bi-x-lg text-danger me-1'></i> Company Logo Error:
                </p> <small>" . $fileUploader->getError(true) . "</small>";
            }
        } else {
            $uploadUrl = $uss->pathToUrl($fileUploader->getUploadedFilepath());
            $uss->options->set("company:logo", $uploadUrl);
        }

        (new Alert())
            ->setOption("message", implode("<hr>", $this->message))
            ->display();
    }

    protected function getFileUploader(): FileUploader 
    {
        $mimes = [
            'image/png',
            'image/jpeg',
            'image/jpg',
            'image/gif',
            'image/webp'
        ];

        $fileUploader = new FileUploader($_FILES['company_icon']);

        $fileUploader
            ->addMimeType($mimes)
            ->setMaxFileSize(1024 * 700) // 700 KB
            ->setUploadDirectory(DashboardImmutable::ASSETS_DIR . '/images')
            ->setFilename("logo");
        
        return $fileUploader;
    }
}