<?php

interface UdashFormInterface {

    public function process(): self;

    public function getRouteUrl(string $pagename): ?string;

    public function isSubmitted(): bool;

    public function isTrusted(): bool;

    public function redirectOnSuccessTo(string $location): void;

}