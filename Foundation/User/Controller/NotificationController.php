<?php

namespace Module\Dashboard\Foundation\User\Controller;

use Module\Dashboard\Bundle\Common\Document;
use Module\Dashboard\Bundle\Common\Paginator;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardController;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormInterface;
use Module\Dashboard\Bundle\User\User;

class NotificationController extends AbstractDashboardController
{
    public const PAGINATOR_KEY = 'index';
    public const ITEMS_PER_PAGE = 10;

    public function composeApplication(DashboardInterface $dashboard, Document $document, ?DashboardFormInterface $form): void
    {
        $user = (new User())->acquireFromSession();
        $paginator = $this->getPaginator($user, $document);
        $offset = ($paginator->getCurrentPage() - 1) * self::ITEMS_PER_PAGE;

        $notifications = $user->notification->get(['hidden' => 0,], $offset, self::ITEMS_PER_PAGE);
        $unseen = $user->notification->count(["seen" => 0, 'hidden' => 0]);

        $document->setContext([
            'notifications' => $notifications,
            'unseen' => $unseen,
            'paginator' => $paginator,
        ]);
    }

    protected function getPaginator(User $user, Document $document): Paginator
    {
        $totalItems = $user->notification->count();
        $currentPage = $_GET[self::PAGINATOR_KEY] ?? 1;
        $urlPattern = $document->getUrl() . sprintf("?%s=", self::PAGINATOR_KEY) . Paginator::NUM_PLACEHOLDER;

        return new Paginator(
            $totalItems,
            self::ITEMS_PER_PAGE,
            $currentPage, 
            $urlPattern
        );
    }
}
