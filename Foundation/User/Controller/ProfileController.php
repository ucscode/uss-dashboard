<?php

namespace Module\Dashboard\Foundation\User\Controller;

class ProfileController implements RouteInterface
{
    public function onload(array $matches)
    {
        $this->onSubmit();
        $this->pageManager->getMenuItem('profile-batch-profile', true)?->setAttr('active', true);
        $template = $this->pageManager->getTemplate();
        $this->dashboard->render($template);
    }

    public function onSubmit(): void
    {
        $user = new User();
        $user->getFromSession();

        if($user->exists() && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $uss = Uss::instance();

            $userInfo = $uss->sanitize($_POST['user']);

            foreach($userInfo as $key => $value) {
                call_user_func([$user, "set{$key}"], $value);
            };

            if($user->persist()) {
                $message = ['Your profile has been updated'];

                $metaInfo = $uss->sanitize($_POST['meta'], Uss::SANITIZE_SCRIPT_TAGS);

                $message[] = $this->changeAvatar($user, $metaInfo, $_FILES['avatar']);

                foreach($metaInfo as $key => $value) {
                    $user->setUserMeta($key, $value);
                }

                (new Alert(implode('<br>', $message)))->display();
            }
        }
    }

    public function changeAvatar(User $user, array &$metaInfo, array $file): ?string
    {
        if($file['error'] != 4) {
            $uploader = new FileUploader($file);
            $uploader->addMimeType([
                'image/png',
                'image/jpeg',
                'image/gif',
                'image/jpg'
            ]);
            $uploader->setMaxFileSize(1000000);
            $uploader->setUploadDirectory(DashboardImmutable::ASSETS_DIR . '/images/profile');
            $uploader->setFilenamePrefix($user->getId());
            if($uploader->uploadFile()) {
                $filepath = $uploader->getUploadedFilepath();
                $fileUrl = Uss::instance()->abspathToUrl($filepath);
                $metaInfo['user.avatar'] = $fileUrl;
            } else {
                return 'Avatar update failed: ' . $uploader->getError();
            }
        }
        return null;
    }
}
