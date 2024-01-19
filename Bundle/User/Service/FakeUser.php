<?php

namespace Module\Dashboard\Bundle\User\Service;

use Faker\Generator;
use Exception;
use Module\Dashboard\Bundle\User\User;
use Uss\Component\Kernel\Uss;

class FakeUser
{
    protected array $users = [];
    protected Generator $faker;
    protected int $timeout = 1000;
    protected int $counter = 0;

    public function __construct()
    {
        $this->faker = \Faker\Factory::create();
    }

    public function create(int $limit = 3): self
    {
        $this->timeout = $limit + 1000;

        while(count($this->users) < $limit) {

            if($this->counter > $this->timeout) {
                throw new Exception(
                    "Possibility of infinite loop whilst trying to create users"
                );
            }

            $this->counter++;

            try {

                $user = new User();
                $user->setEmail($this->faker->email());
                $user->setUsername($this->faker->userName());
                $user->setPassword("12345678", true);
                $user->setUsercode(Uss::instance()->keygen(7));

                if($user->persist()) {
                    $this->users[] = $user;
                }

            } catch(Exception $e) {

            }
        }

        return $this;
    }

    public function forEachUser(callable $func): void
    {
        foreach($this->users as $user) {
            $func($user);
        }
    }
}