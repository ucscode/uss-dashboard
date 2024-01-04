<?php

namespace Module\Dashboard\Bundle\User\Service;

use Module\Dashboard\Bundle\User\User;

class Mailer
{
    public function __construct(protected User $user)
    {

    }
}
