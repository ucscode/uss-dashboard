<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users\Process;

use Module\Dashboard\Bundle\FileUploader\FileUploader;
use Module\Dashboard\Bundle\Flash\Flash;
use Module\Dashboard\Bundle\Flash\Toast\Toast;
use Module\Dashboard\Bundle\Immutable\DashboardImmutable;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardForm;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormSubmitInterface;
use Module\Dashboard\Bundle\Immutable\DeviceImmutable;
use Uss\Component\Kernel\Uss;

abstract class AbstractUserFormSubmit extends AbstractErrorManagement implements DashboardFormSubmitInterface
{
    public function onFilter(array &$resource, AbstractDashboardForm $form): void
    {
        $this->postContext = $resource;
        $this->roles = array_keys(
            array_filter(
                $resource['roles'],
                fn ($value) => !empty($value)
            )
        );
        unset($resource['roles']);
        $resource = array_map('trim', $resource);
    }

    public function onValidate(?array &$resource, AbstractDashboardForm $form): void
    {
        if($resource !== null) {
            
            $resource['email'] = $this->handleEmailError($resource['email'], $form);
            $resource['username'] = $this->handleUsernameError($resource['username'], $form);

            !array_key_exists('parent', $resource) ? null :
                $resource['parent'] = $this->handleParentError($resource['parent'], $form);

            !array_key_exists('password', $resource) ? null :
                $resource['password'] = $this->handlePasswordError($resource['password'], $form);
        }
    }

    public function onPersist(mixed &$response, AbstractDashboardForm $form): void
    {        
        if(!$form->getPersistenceStatus()) {
            $this->handlePersistionError();
        }
        
        if($response) {
            if($this->client->isAvailable()) {
                $this->client->roles->set($this->roles);
                $this->updateAvatar($_FILES['avatar']);
            }
        }
        
        $form->setProperty('history.replaceState', false);
    }

    protected function updateAvatar(array $file): void
    {
        if($file['error'] !== UPLOAD_ERR_NO_FILE) {

            $uploader = new FileUploader($file);

            $uploader->setMimeTypes(DeviceImmutable::IMAGE_MIME_TYPES);
            $uploader->setMaxFileSize(1024 * 1024);
            $uploader->setUploadDirectory(DashboardImmutable::ASSETS_DIR . '/images/profile');
            $uploader->setFilenamePrefix($this->client->getId() . "-");
            $uploader->setFilename("avatar");

            if($uploader->uploadFile()) {
                $avatar = $uploader->getUploadedFilepath();
                $this->client->meta->set('user.avatar', Uss::instance()->pathToUrl($avatar));
                return;
            }

            $toast = (new Toast())
                ->setBackground(Toast::BG_DANGER)
                ->setMessage("Avatar Update Failed!");

            Flash::instance()->addToast($toast);
        }
    }

    protected function easyToast(string $message, ?string $background = null, int $delay = 0): Toast
    {
        $background ??= Toast::BG_DANGER;
        $toast = (new Toast())
            ->setBackground($background)
            ->setMessage($message)
            ->setDelay($delay);
        Flash::instance()->addToast($toast);
        return $toast;
    }
}