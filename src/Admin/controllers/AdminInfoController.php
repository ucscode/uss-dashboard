<?php

use Ucscode\DOMTable\DOMTable;
use Ucscode\DOMTable\DOMTableInterface;
use Ucscode\UssElement\UssElement;

class AdminInfoController implements RouteInterface
{
    public function __construct(
        protected PageManager $pageManager,
        protected DashboardInterface $dashboard
    ) {

    }

    public function onload(array $matches)
    {
        $this->pageManager->getMenuItem(
            AdminDashboardInterface::PAGE_INFO,
            true
        )?->setAttr('active', true);

        $table = $this->createTable($this->getInfo());
        $result = $table->build()->getHTML(true);

        $this->dashboard->render($this->pageManager->getTemplate(), [
            "table" => $result
        ]);
    }

    public function getInfo(): array
    {
        $uss = Uss::instance();
        $time = new DateTime();
        $timezone = $time->getTimezone();
        $location = $timezone->getLocation();

        $countryManager = new CountryManager(false);

        return [
            'Installation Directory' => ROOT_DIR,
            'Domain Name' => $_SERVER['SERVER_NAME'],
            'HTTPS' => $_SERVER['SERVER_PORT'] === 80 ? 'Disabled' : 'Enabled',
            'Remote Address' => $_SERVER['REMOTE_ADDR'],
            'Website URL' => $uss->abspathToUrl(ROOT_DIR),
            'Admin Email' => $uss->options->get('company:email'),
            'Current Time' => $time->format("Y-m-d h:i:s A"),
            'TimeZone' => $timezone->getName(),
            'Country Code' => $location['country_code'],
            'Country Name' => $countryManager->getCountryName($location['country_code']) ?? 'NULL',
            'Latitude' => $location['latitude'],
            'Longitude' => $location['longitude'],
            'Server Software' => $_SERVER['SERVER_SOFTWARE'],
            'PHP OS' => PHP_OS,
            'PHP Version' => PHP_VERSION,
            'MYSQLI Version' => $uss->mysqli->server_info,
            'Database Host' => DB_HOST,
            'Database Username' => DB_USER,
            'Database Password' => str_repeat('*', 6),
            'Database Name' => DB_NAME,
            'Database Table Prefix' => DB_PREFIX,
            'Author Name' => UssImmutable::AUTHOR,
            'Author Website' => UssImmutable::AUTHOR_WEBSITE,
            'Author Email' => UssImmutable::AUTHOR_EMAIL,
            'Project Name' => UssImmutable::PROJECT_NAME,
            'Project Repository' => UssImmutable::GITHUB_REPO,
            'Project Website' => UssImmutable::PROJECT_WEBSITE,
        ];
    }

    public function createTable(array $info): DOMTable
    {
        $table = new DOMTable(AdminDashboardInterface::PAGE_INFO);
        $table->setColumns(["info", "value"]);
        $data = [];

        foreach($info as $key => $value) {
            $data[] = [
                "info" => $key,
                "value" => $value
            ];
        }

        $table->setData($data, new class () implements DOMTableInterface {
            public function foreachItem(array $item): ?array
            {
                switch($item['info']) {
                    case "Author Website":
                    case "Project Repository":
                    case "Project Website":
                        $el = new UssElement(UssElement::NODE_A);
                        $el->setAttribute('href', $item['value']);
                        $el->setContent($item['value'] . " <i class='bi bi-box-arrow-up-right ms-1'></i>");
                        $el->setAttribute('target', '_blank');
                        $item['value'] = $el->getHTML(true);
                        break;
                }
                return $item;
            }
        });

        $table->setItemsPerPage(100);
        $table->getTheadElement()->setAttribute('class', 'd-none');
        $table->getTableElement()->addAttributeValue("class", "table-striped");
        return $table;
    }
}
