<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users\Interface;

use Module\Dashboard\Bundle\Crud\Kernel\Interface\CrudKernelInterface;
use Module\Dashboard\Bundle\User\User;
use Ucscode\UssForm\Form\Form;

interface UserControllerInterface
{
    public function getCrudKernel(): CrudKernelInterface;
    public function getForm(): ?Form;
    public function getClient(): ?User;
}