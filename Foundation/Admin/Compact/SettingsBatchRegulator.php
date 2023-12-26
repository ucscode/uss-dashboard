<?php

use Ucscode\TreeNode\TreeNode;

final class SettingsBatchRegulator implements EventInterface
{
    public function __construct(protected AdminDashboard $dashboard) {}
    
    public function eventAction(array|object $data): void
    {
        $this->orderBatch();
        $this->inspectActiveItem();
    }

    public function orderBatch(): void
    {
        $this->dashboard->settingsBatch->sortChildren(function(TreeNode $a, TreeNode $b) {
            return ($a->getAttr('order') ?? 0) <=> ($b->getAttr('order') ?? 0);
        });
    }

    public function inspectActiveItem(): void
    {
        $settingsNavigation = $this->dashboard
            ->pageRepository
            ->getPageManager(AdminDashboardInterface::PAGE_SETTINGS)
            ?->getMenuItem(AdminDashboardInterface::PAGE_SETTINGS, true);

        if($settingsNavigation) {
            foreach($this->dashboard->settingsBatch->children as $treeNode) {
                if($treeNode->getAttr('active')) {
                    $settingsNavigation->setAttr('active', true);
                    break;
                }
            }
        }
    }
}