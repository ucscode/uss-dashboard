<?php

namespace Module\Dashboard\Foundation\Admin\Controller;

use DateTime;
use Module\Dashboard\Bundle\Common\Paginator;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardController;
use Ucscode\DOMTable\DOMTable;
use Ucscode\UssElement\UssElement;
use Uss\Component\Kernel\Uss;
use Uss\Component\Kernel\UssImmutable;
use Uss\Component\Manager\CountryManager;

class SystemInfoController extends AbstractDashboardController
{
    protected DOMTable $domTable;

    public function onload(array $context): void
    {
        parent::onload($context);

        $tableWrapper = $this->createSystemInfoTable();

        $paginator = new Paginator(
            $this->domTable->gettotalItems(),
            $this->domTable->getItemsPerPage(),
            $this->domTable->getCurrentPage(),
            '?page=' . Paginator::NUM_PLACEHOLDER
        );

        $this->document->setContext([
            'tableWrapper' => $tableWrapper,
            'paginator' => $paginator,
        ]);
    }

    protected function createSystemInfoTable(): UssElement
    {
        $this->domTable = new DOMTable('system-info');
        $this->domTable->setColumns(['name', 'entry']);
        $data = [];
        foreach($this->getInfo() as $key => $value) {
            $data[] = [
                'name' => $key,
                'entry' => $value,
            ];
        }
        $this->domTable->setCurrentPage($_GET['page'] ?? 1);
        $this->domTable->getTableElement()->addAttributeValue('class', 'table-striped');
        $this->domTable->setData($data);
        return $this->domTable->build();
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
            'Website URL' => $uss->pathToUrl(ROOT_DIR),
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
            'Database Host' => $_ENV['DB_HOST'],
            'Database Username' => $_ENV['DB_USERNAME'],
            'Database Password' => str_repeat('*', 6),
            'Database Name' => $_ENV['DB_NAME'],
            'Database Table Prefix' => $_ENV['DB_PREFIX'],
            'Author Name' => UssImmutable::AUTHOR,
            'Author Website' => $this->printUrl(UssImmutable::AUTHOR_WEBSITE),
            'Author Email' => $this->printUrl(UssImmutable::AUTHOR_EMAIL, null, 'mailto:'),
            'Project Name' => UssImmutable::PROJECT_NAME,
            'Project Repository' => $this->printUrl(UssImmutable::GITHUB_REPO),
            'Project Website' => $this->printUrl(UssImmutable::PROJECT_WEBSITE),
        ];
    }

    protected function printUrl(string $url, ?string $display = null, string $urlPrefix = ''): string
    {
        $display ??= $url;
        return sprintf(
            "<a href='%s%s' target='_blank'>%s</a>",
            $urlPrefix,
            $url,
            $display
        );
    }
}
