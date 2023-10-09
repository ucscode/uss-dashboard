<?php

/**
 * @author Uchenna Ajah <uche23mail@gmail.com>
 */
interface UdBaseInterface
{
    public const BASE_DIR = UD_DIR;
    public const ASSETS_DIR = self::BASE_DIR . "/assets";
    public const SRC_DIR = self::BASE_DIR . "/src";
    public const VIEW_DIR = self::BASE_DIR . "/view";
    public const RES_DIR = self::BASE_DIR . "/bundles";
    public const CENTRAL_DIR = self::RES_DIR . "/central";
    public const CLASS_DIR = self::RES_DIR . "/class";

    public function setConfig(string $property, mixed $value): void;
    public function getConfig(?string $property): mixed;
    public function removeConfig(string $property): void;
    public function addArchive(UdArchive $archive): void;
    public function getArchive(string $pagename): ?UdArchive;
    public function getArchiveUrl(string $pagename): ?string;
    public function removeArchive(string $pageName): null|bool;
    public function enableFirewall(bool $enable = true): void;
    public function render(string $template, array $options = []): void;
    public function fetchData(string $tablename, mixed $value, $column = 'id'): ?array;

}
