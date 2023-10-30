<?php

class FakeUser
{
    protected array $roles = [
        RoleImmutable::ROLE_ADMIN,
        RoleImmutable::ROLE_SUPERADMIN,
        RoleImmutable::ROLE_USER,
        RoleImmutable::ROLE_GUEST,
        RoleImmutable::ROLE_MANAGER
    ];

    public function create(int $number = 1)
    {
        for($x = 0; $x < $number; $x++) {
            $this->createFakeUser();
        }
    }

    protected function createFakeUser(): void
    {
        $faker = Faker\Factory::create();
        $user = new User();
        $user->setEmail($faker->email());
        $user->setUsername(str_replace('.', '-', $faker->userName()));
        $user->setPassword(12345678, true);
        $user->setUsercode(Uss::instance()->keygen(5));
        if($user->persist()) {
            for($x = 0; $x < rand(0, 3); $x++) {
                $key = array_rand($this->roles);
                $user->setRole($this->roles[$key]);
            }
        }
    }
}
