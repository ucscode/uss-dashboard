<?php

namespace Module\Dashboard\Foundation\System\Compact;

use Uss\Component\Kernel\Uss;
use Module\Dashboard\Bundle\Immutable\DashboardImmutable;
use Module\Dashboard\Bundle\Immutable\RoleImmutable;
use Exception;

final class DatabaseGenerator
{
    public function __construct(protected Uss $uss)
    {
        $this->checkDatabaseEnabled();
        $this->configureDatabase();
        $this->setDatabaseOptions();
    }

    private function checkDatabaseEnabled(): void
    {
        if(!filter_var($_ENV['DB_ENABLED'], FILTER_VALIDATE_BOOLEAN)) {
            $message = [
                "subject" => "(Dashboard) Database Connection Disabled",
                "message" => "Please enable the database connection to activate GUI",
                "message_class" => "mb-5",
                "image_style" => "width: 150px",
                "image" => $this->uss->pathToUrl(DashboardImmutable::ASSETS_DIR . '/images/database-error-icon.webp'),
            ];
            $this->uss->render('@Uss/error.html.twig', $message); // exits internally
        };
    }


    private function configureDatabase(): void
    {
        $databaseTables = $this->getTableStatements();

        foreach($databaseTables as $SQL) {
            try {
                $SQL = str_replace('%{prefix}', $_ENV['DB_PREFIX'], $SQL);
                $result = $this->uss->mysqli->query($SQL);
                if(!$result) {
                    throw new Exception($this->uss->mysqli->error);
                }
            } catch(Exception $e) {
                $this->uss->render('@Uss/error.html.twig', [
                    "subject" => "(Dashboard) Database Setup Error",
                    "message" => filter_var($_ENV['APP_DEBUG'], FILTER_VALIDATE_BOOLEAN) ? $e->getMessage() : 'MYSQL Error Number: ' . $this->uss->mysqli->errno
                ]);
                exit();
            };
        };
    }

    private function getTableStatements(): array
    {
        return [
            "CREATE TABLE IF NOT EXISTS %{prefix}users (
                id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                email VARCHAR(255) NOT NULL UNIQUE,
                username VARCHAR(25) DEFAULT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                register_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
                usercode VARCHAR(12) NOT NULL UNIQUE,
                last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
                parent INT UNSIGNED DEFAULT NULL,
                FOREIGN KEY(parent) REFERENCES %{prefix}users(id) ON DELETE SET NULL
            )",

            "CREATE TABLE IF NOT EXISTS %{prefix}notifications (
                id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                category VARCHAR(100) DEFAULT NULL,
                userid INT UNSIGNED NOT NULL,
                period TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                message VARCHAR(5000),
                seen TINYINT NOT NULL DEFAULT 0,
                redirect_url VARCHAR(255),
                avatar_url VARCHAR(255),
                hidden TINYINT NOT NULL DEFAULT 0,
                internal_note TINYTEXT,
                FOREIGN KEY(userid) REFERENCES %{prefix}users(id) ON DELETE CASCADE
            )"
        ];
    }

    private function setDatabaseOptions(): void
    {
        $configuration = [
            'company:logo' => $this->uss->templateContext['page_logo'],
            'company:name' => $this->uss->templateContext['page_title'],
            'company:slogan' => $this->uss->templateContext['page_slogan'],
            'company:description' => $this->uss->templateContext['page_description'],
            'company:email' => 'admin@example.com',
            'company:email-alt' => null,
            'user:disable-signup' => 0,
            'user:collect-username' => 0,
            'user:confirm-email' => 0,
            'user:readonly-email' => 0,
            'user:reconfirm-email' => 1,
            'user:default-role' => RoleImmutable::ROLE_USER,
            'user:affiliation' => 0,
            'user:remove-inactive-after-day' => 7, // 0 or null to ignore
            'smtp:enabled' => 0,
        ];

        foreach($configuration as $key => $value) {
            if(is_null($this->uss->options->get($key, null, true))) {
                $this->uss->options->set($key, $value);
            };
        };
    }
}
