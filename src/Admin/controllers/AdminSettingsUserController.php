<?php

use Ucscode\UssForm\UssFormField;
use Ucscode\UssForm\UssForm;

class AdminSettingsUserController implements RouteInterface
{
    public function __construct(
        protected PageManager $pageManager,
        protected DashboardInterface $dashboard
    ){}

    public function onload(array $matches)
    {
        $this->pageManager
            ->getMenuItem(AdminDashboardInterface::PAGE_SETTINGS_USERS, true)
            ?->setAttr('active', true);
            
        $form = $this->pageManager->getForm();

        $this->updateRoleField($form->getField("user[default-roles][]"));

        $this->dashboard->render($this->pageManager->getTemplate(), [
            'form' => $form
        ]);
    }

    protected function updateRoleField(UssFormField $roleField): void
    {
        (new Event())->addListener(

            "dashboard:render", 

            new class ($roleField) implements EventInterface 
            {
                public function __construct(protected UssFormField $roleField) {}

                public function eventAction(array|object $data): void
                {
                    foreach((new DashboardFactory())->getPermissions() as $index => $permission) {
                        if(!$index) {
                            $this->roleField
                                ->setLabelValue($permission)
                                ->setWidgetValue($permission)
                                ->setRequired(false);
                            continue;
                        };
                        $this->roleField
                            ->createSecondaryField($permission, UssForm::TYPE_CHECKBOX)
                            ->setLabelValue($permission)
                            ->setWidgetValue($permission)
                            ->setRequired(false);
                    }
                }
            }, 

            -9
        );
    }
}