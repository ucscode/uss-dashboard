<?php

namespace Module\Dashboard\Foundation\Admin\Form\Settings;

use Module\Dashboard\Bundle\Exception\DashboardException;
use Module\Dashboard\Bundle\FileUploader\FileUploader;
use Module\Dashboard\Bundle\Flash\Flash;
use Module\Dashboard\Bundle\Flash\Toast\Toast;
use Module\Dashboard\Bundle\Immutable\DashboardImmutable;
use Module\Dashboard\Bundle\Immutable\DeviceImmutable;
use Module\Dashboard\Foundation\Admin\Form\Settings\Abstract\AbstractSystemSettingsForm;
use Uss\Component\Kernel\Uss;
use Uss\Component\Kernel\UssImmutable;

class SystemSettingsForm extends AbstractSystemSettingsForm
{
    protected function buildForm(): void
    {
        $this->attribute->setEnctype('multipart/form-data');
        $this->createPrimaryFields();
        $this->createAvatarFields();
    }

    protected function validateResource(array $filteredResource): ?array
    {
        $validNonce = Uss::instance()->nonce(
            $_SESSION[UssImmutable::APP_SESSION_KEY], 
            $filteredResource['nonce'] ?? ''
        );
        
        if($validNonce) {
            unset($filteredResource['nonce']);
            $file = $_FILES['logo'];
            if($file['error'] !== UPLOAD_ERR_NO_FILE) {
                $filteredResource['company']['logo'] = $this->uploadLogo($file);
            }
            return $filteredResource['company'];
        }

        return null;
    }

    protected function persistResource(?array $validatedResource): mixed
    {
        if($validatedResource) {

            $uss = Uss::instance();
            $persisted = [];

            foreach($validatedResource as $key => $value) {
                $name = 'company:' . $key;
                $persisted[] = $uss->options->set($name, $value);
            };
            
            if(in_array(false, $persisted, true)) {
                throw new DashboardException("One or more data could not be saved!");
            }

            $toast = (new Toast())
                ->setBackground(Toast::BG_SUCCESS)
                ->setMessage("Updated was successful")
            ;

            Flash::instance()->addToast($toast);
        }
        return null;
    }

    protected function resolveSubmission(mixed $presistedResource): void
    {}

    protected function uploadLogo(array $file): ?string
    {
        $uploader = new FileUploader($file);
        $uploader->setUploadDirectory(DashboardImmutable::ASSETS_DIR . "/images");
        $uploader->setFilename("logo");
        $uploader->setMimeTypes(DeviceImmutable::IMAGE_MIME_TYPES);
        $uploader->setMaxFileSize(1024 * 800);
        $uploaded = $uploader->uploadFile();
        if($uploaded) {
            $filepath = $uploader->getUploadedFilepath();
            return Uss::instance()->pathToUrl($filepath);
        }
        throw new DashboardException($uploader->getError(true));
    }
}