<?php

defined('ROOT_DIR') || die(':NOTIFICATION');

class NotificationController implements RouteInterface
{
    private $parseDown;

    public function __construct(private UdPage $page)
    {
        $this->parseDown = new Parsedown;
    }

    public function onload($pageInfo)
    {

        $ud = Ud::instance();

        $user = (new User())->getFromSession();
        
        if($user) {

            $totalItems = $user->countNotifications();
            $itemsPerPage = 20;

            $currentPage = $this->getCurrentPage( $totalItems, $itemsPerPage );
            $urlPattern = $ud->getPageUrl('notification') . '?page=(:num)';

            $startFrom = ($currentPage - 1) * $itemsPerPage;

            $notifications = $user->getNotifications(null, $startFrom, $itemsPerPage);

            $notifications = array_map(function($data) {
                $data['message'] = $this->parseDown->text($data['message']);
                return $data;
            }, $notifications);

            $paginator = new Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);
            
            $paginator->setMaxPagesToShow(3);

        } else {

            $paginator = null;

        }
        
        $ud->render($this->page->get('template'), [
            'notifications' => $notifications,
            'paginator' => $paginator
        ]);

    }

    private function getCurrentPage(int $totalItems, int $itemsPerPage) {
        $index = $_GET['page'] ?? null;
        if(!is_numeric($index)) {
            $index = 1;
        }
        $index = abs($index);
        if($index < 1 || !$totalItems) {
            $index = 1;
        } else {
            $maxPage = ceil($totalItems / $itemsPerPage);
            if($index > $maxPage) {
                $index = $maxPage;
            };
        };
        return $index;
    }

};
