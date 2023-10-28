<?php

use Ucscode\UssElement\UssElement;

interface CrudEditInterface
{
    public function createUI(): UssElement;

    public function setField(string $name, CrudField $field): self;

    public function getField(string $name): ?CrudField;

    public function setSubmitUrl(string $url): self;

    public function getSubmitUrl(): ?string;
}
