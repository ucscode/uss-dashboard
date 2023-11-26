<?php

final class DatabaseConfigurator
{
    public function __construct()
    {
        $this->checkDatabaseEnabled();
        $this->configureDatabase();
        $this->setDatabaseOptions();
    }

    private function checkDatabaseEnabled(): void
    {
        $uss = Uss::instance();
        if(!DB_ENABLED) {
            $message = [
                "subject" => "Database Connection Disabled",
                "message" => sprintf("<span class='%s'>PROBLEM</span> : define('DB_ENABLED', <span class='%s'>false</span>)", 'text-danger', 'text-primary'),
                "message_class" => "mb-5",
                "image" => $uss->abspathToUrl(DashboardImmutable::ASSETS_DIR . '/images/database-error-icon.webp'),
                "image_style" => "width: 150px"
            ];
            $uss->render('@Uss/error.html.twig', $message);
            exit();
        };
    }


    private function configureDatabase(): void
    {
        $uss = Uss::instance();
        $databaseTables = $this->getTableStatements();

        foreach($databaseTables as $SQL) {
            try {
                $SQL = $uss->replaceVar($SQL, ['prefix' => DB_PREFIX]);
                $result = $uss->mysqli->query($SQL);

                if(!$result) {
                    throw new \Exception($uss->mysqli->error);
                }
            } catch(\Exception $e) {
                $uss->render('@Uss/error.html.twig', [
                    "subject" => "Ud: Database Setup Error",
                    "message" => UssImmutable::DEBUG ? $e->getMessage() : 'MYSQL Error Number: ' . $uss->mysqli->errno
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
                origin INT,
                model VARCHAR(100) DEFAULT NULL COMMENT 'TYPE: Comment, Reply, Module-Name...',
                userid INT UNSIGNED NOT NULL,
                period TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                message VARCHAR(5000),
                viewed TINYINT NOT NULL DEFAULT 0,
                redirect VARCHAR(255) DEFAULT NULL COMMENT 'URL',
                image VARCHAR(255),
                hidden TINYINT NOT NULL DEFAULT 0,
                FOREIGN KEY(userid) REFERENCES %{prefix}users(id) ON DELETE CASCADE
            )"
        ];
    }

    private function setDatabaseOptions(): void
    {
        $uss = Uss::instance();

        $configuration = [
            'company:logo' => Uss::$globals['icon'],
            'company:name' => Uss::$globals['title'],
            'company:headline' => Uss::$globals['headline'],
            'company:description' => Uss::$globals['description'],
            'company:email' => 'admin@example.com',
            'company:email-alt' => null,
            'user:disable-signup' => 0,
            'user:collect-username' => 0,
            'user:confirm-email' => 0,
            'user:lock-email' => 0,
            'user:reconfirm-email' => 1,
            'user:default-role' => 'member',
            'user:affiliation' => 0,
            'user:remove-inactive-after-day' => 7, // 0 or null to ignore
            'smtp:state' => 'default'
        ];

        foreach($configuration as $key => $value) {
            if(is_null($uss->options->get($key, null, true))) {
                $uss->options->set($key, $value);
            };
        };
    }
}
