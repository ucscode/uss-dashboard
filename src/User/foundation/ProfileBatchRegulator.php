<?php

use Ucscode\TreeNode\TreeNode;

final class ProfileBatchRegulator implements EventInterface 
{
    public function __construct(protected UserDashboard $dashboard) {}

    public function eventAction(array|object $data): void
    {
        $this->dashboard->profileBatch->sortChildren(function(TreeNode $a, TreeNode $b) {
            return ($a->getAttr('order') ?? 0) <=> ($b->getAttr('order') ?? 0);
        });
        $this->inspectActiveItem();
    }

    public function inspectActiveItem(): void
    {
        $pageManager = $this->dashboard
            ->pageRepository
            ->getPageManager(UserDashboardInterface::PAGE_USER_PROFILE);

        foreach($this->dashboard->profileBatch->children as $child) {
            if($child->getAttr('active') && $pageManager) {
                $item = $pageManager->getMenuItem(UserDashboardInterface::PAGE_USER_PROFILE, true);
                if($item) {
                    $item->setAttr('active', true);
                }
            }
        };
    }
}