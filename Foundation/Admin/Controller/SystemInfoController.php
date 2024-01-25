<?php

namespace Module\Dashboard\Foundation\Admin\Controller;

use DateTime;
use Module\Dashboard\Bundle\Common\Document;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardController;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormInterface;
use Uss\Component\Database;
use Uss\Component\Kernel\Uss;
use Uss\Component\Kernel\UssImmutable;
use Uss\Component\Manager\CountryManager;

class SystemInfoController extends AbstractDashboardController
{
    public function composeApplication(DashboardInterface $dashboard, Document $document, ?DashboardFormInterface $form): void
    {  
        
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
            'Database Host' => Database::HOST,
            'Database Username' => Database::USERNAME,
            'Database Password' => str_repeat('*', 6),
            'Database Name' => Database::NAME,
            'Database Table Prefix' => Database::PREFIX,
            'Author Name' => UssImmutable::AUTHOR,
            'Author Website' => UssImmutable::AUTHOR_WEBSITE,
            'Author Email' => UssImmutable::AUTHOR_EMAIL,
            'Project Name' => UssImmutable::PROJECT_NAME,
            'Project Repository' => UssImmutable::GITHUB_REPO,
            'Project Website' => UssImmutable::PROJECT_WEBSITE,
        ];
    }
}
