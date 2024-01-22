<?php

namespace Module\Dashboard\Bundle\Flash\Interface;

use Module\Dashboard\Bundle\Flash\Modal\Modal;
use Module\Dashboard\Bundle\Flash\Toast\Toast;

interface FlashInterface
{
    public function dump(): void;
    public function setFilepath(string $filepath): self;
    public function addModal(Modal $modal, ?string $name): self;
    public function addToast(Toast $toast, ?string $name): self;
}