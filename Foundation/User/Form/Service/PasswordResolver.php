<?php

namespace Module\Dashboard\Foundation\User\Form\Service;

use Module\Dashboard\Bundle\Common\Password;

class PasswordResolver
{
    public function resolve(?string $password): array
    {
        $password = new Password($password);
        $strength = $password->calculateStrength();

        $requirements = [];
        $resolved = [];

        $factors = [
            'minLength' => $password->getMinLength() . ' characters',
            'lowerCase' => 'lowercase letter',
            'upperCase' => 'uppercase letter',
            'number' => 'number',
            'specialChar' => 'special character'
        ];

        $errorLevel = [
            [
                "required",
                "text-danger"
            ],
            [
                "too weak",
                "text-danger"
            ],
            [
                "weak",
                "text-danger"
            ],
            [
                "fair",
                "text-info"
            ],
            [
                "strong",
                "text-success"
            ],
            [
                "very strong",
                "text-success"
            ],
        ];

        $passwordResolver = [
            'strength' => $strength
        ];

        foreach($factors as $key => $value) {
            $approved = match($key) {
                'minLength' => $password->hasMinLength(),
                'lowerCase' => $password->hasLowerCase(),
                'upperCase' => $password->hasUpperCase(),
                'number' => $password->hasNumber(),
                default => $password->hasSpecialChar(),
            };

            $approved ?
                $resolved[$key] = $value :
                $requirements[$key] = $value;
        }

        $passwordResolver['requirements'] = $requirements;
        $passwordResolver['resolved'] = $resolved;
        $passwordResolver['errorLevel'] = $errorLevel[$strength][0];
        $passwordResolver['errorMessage'] = sprintf("Password is %s", $passwordResolver['errorLevel']);
        $passwordResolver['appearance'] = $errorLevel[$strength][1];
        $passwordResolver['instance'] = $password;

        return $passwordResolver;
    }
}