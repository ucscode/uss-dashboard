<?php

defined('ROOT_DIR') || die(':NOTIFICATION');

class NotificationController implements RouteInterface
{
    private $parseDown;

    public function __construct(private UdArchive $page)
    {
        $this->parseDown = new Parsedown();
    }

    public function onload($pageInfo)
    {
        $ud = Ud::instance();

        $user = (new User())->getFromSession();

        $_SERVER['REQUEST_METHOD'] === 'GET' ? $this->getRequest($ud, $user) : $this->postRequest($ud, $user);
    }

    protected function getRequest(Ud $ud, ?User $user): void
    {
        if($user) {

            $totalItems = $user->countNotifications();
            $itemsPerPage = 20;

            $currentPage = $this->getCurrentPage($totalItems, $itemsPerPage);
            $urlPattern = $ud->getArchiveUrl('notification') . '?page=(:num)';

            $startFrom = ($currentPage - 1) * $itemsPerPage;

            $notifications = $user->getNotifications([
                'hidden' => 0
            ], $startFrom, $itemsPerPage);

            $notifications = array_map(function ($data) {
                $data['message'] = $this->parseDown->text($data['message']);
                return $data;
            }, $notifications);

            $paginator = new Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);

            $paginator->setMaxPagesToShow(3);

        } else {

            $paginator = $notifications = null;

        }

        $ud->render($this->page->get('template'), [
            'notifications' => $notifications,
            'paginator' => $paginator
        ]);
    }

    protected function postRequest(Ud $ud, ?User $user): void
    {
        $nonce = $_POST['notificationNonce'] ?? null;

        if(!$user) {
            if(empty($nonce)) {
                $indexArchiveUrl = $ud->getArchiveUrl('index');
                header("location: " . $indexArchiveUrl);
                exit;
            }
        }

        $uss = Uss::instance();

        $trusted = $uss->nonce('Ud', $nonce);

        if($trusted) {

            $_POST = $uss->sanitize($_POST);

            $data = array_filter($_POST, function ($key) {
                return in_array($key, [
                    'viewed',
                    'hidden',
                ]);
            }, ARRAY_FILTER_USE_KEY);

            $parser = $_POST['id'] == '*' ? [] : $_POST['id'];

            $updated = $user->updateNotification($data, $parser);

            $remaining = $user->countNotifications([
                'hidden' => 0,
                'viewed' => 0
            ]);

            $uss->exit($updated, $remaining);

        }
    }

    private function getCurrentPage(int $totalItems, int $itemsPerPage)
    {
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
