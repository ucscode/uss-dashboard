<?php

use Ucscode\Form\FormField;
use Ucscode\Form\Form;

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

    protected function updateRoleField(FormField $roleField): void
    {
        (new Event())->addListener(

            "dashboard:render", 

            new class ($roleField) implements EventInterface 
            {
                public function __construct(protected FormField $roleField) {}

                public function eventAction(array|object $data): void
                {
                    $defaultRoles = Uss::instance()->options->get("user:default-roles");
                    
                    foreach((new DashboardFactory())->getPermissions() as $index => $permission) {
                        if(!$index) {
                            $this->roleField
                                ->setLabelValue($permission)
                                ->setWidgetValue($permission)
                                ->setRequired(false)
                                ->setWidgetChecked(in_array($permission, $defaultRoles));
                            continue;
                        };
                        $this->roleField
                            ->createSecondaryField($permission, Form::TYPE_CHECKBOX)
                            ->setLabelValue($permission)
                            ->setWidgetValue($permission)
                            ->setRequired(false)
                            ->setWidgetChecked(in_array($permission, $defaultRoles));
                    }
                }
            }, 

            -9
        );
    }
}