<?php

class UserPasswordController implements RouteInterface
{
    public function __construct(
        protected PageManager $pageManager,
        protected DashboardInterface $dashboard
    ) {

    }

    public function onload(array $matches)
    {
        $this->managePassword();
        $this->pageManager->getMenuItem('profile-batch-password', true)?->setAttr('active', true);
        $this->dashboard->render($this->pageManager->getTemplate());
    }

    public function managePassword(): void
    {
        $user = new User();
        if($user->getFromSession() && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user'])) {
            $uss = Uss::instance();
            $passwordInfo = $uss->sanitize($_POST['user'], Uss::SANITIZE_SQL);
            $valid = $user->isValidPassword($passwordInfo['old_password']);

            if(!$valid) {
                $alert = 'Old Password is not valid';
            } elseif(strlen($passwordInfo['new_password']) < 6) {
                $alert = 'Password should be at least 6 characters';
            } elseif($passwordInfo['old_password'] == $passwordInfo['new_password']) {
                $alert = 'Old and new password cannot be the same';
            } elseif($passwordInfo['new_password'] !== $passwordInfo['confirm_password']) {
                $alert = 'Password does not match';
            } else {
                $user->setPassword($passwordInfo['new_password'], true);
                if($user->persist()) {
                    $user->saveToSession();
                    $alert = "Your password has been updated successfully";
                } else {
                    $alert = "Error: Password could not be updated";
                }
            }

            (new Alert())
                ->setOption('message', $alert)
                ->display();
        }
    }
}
