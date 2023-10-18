<?php

class UserProfileController implements RouteInterface
{
    public function __construct(
        protected Archive $archive,
        protected DashboardInterface $dashboard
    ) {

    }

    public function onload(array $matches)
    {
        $this->onSubmit();
        $this->archive->getMenuItem('profilePill', true)?->setAttr('active', true);
        $this->dashboard->render($this->archive->get('template'));
    }

    public function onSubmit(): void
    {
        $user = new User();
        $user->getFromSession();

        if($user->exists() && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $uss = Uss::instance();
            
            $userInfo = $uss->sanitize($_POST['user']);
            foreach($userInfo as $key => $value) {
                $user->{$key} = $value;
            };

            $metaInfo = $uss->sanitize($_POST['meta'], Uss::SANITIZE_SCRIPT_TAGS | Uss::SANITIZE_SQL);
            $this->changeAvatar($user, $metaInfo);
            foreach($metaInfo as $key => $value) {
                $user->setMeta($key, $value);
            }

            $user->persist();
        }
    }

    public function changeAvatar(User $user, array &$metaInfo): void
    {
        $uploader = new FileUploader($_FILES['avatar'] ?? null);
        $uploader->addMimeType([
            'image/png',
            'image/jpeg',
            'image/gif',
            'image/jpg'
        ]);
        $uploader->setMaxFileSize(6039);
        $uploader->setUploadDirectory(DashboardImmutable::ASSETS_DIR . '/images/profile');
        $uploader->setFilenamePrefix($user->id);
        if($uploader->uploadFile()) {
            $filepath = $uploader->getUploadedFilepath();
            $fileUrl = Uss::instance()->abspathToUrl($filepath);
            $metaInfo['user.avatar'] = $fileUrl;
        } else {

        };
    }
}
