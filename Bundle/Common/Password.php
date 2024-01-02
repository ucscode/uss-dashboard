<?php

namespace Module\Dashboard\Bundle\Common;

class Password
{
    protected int $minLength = 8;

    protected array $strength = [
        'minLength' => 0,
        'lowerCase' => 0,
        'upperCase' => 0,
        'number' => 0,
        'specialChar' => 0
    ];

    public function __construct(protected ?string $password)
    {
        $this->inspectPassword();
    }

    public function setMinLength(int $length): self
    {
        $this->minLength = abs($length ?: 8);
        return $this;
    }

    public function hasUpperCase(): bool
    {
        return $this->strength['upperCase'];
    }

    public function hasLowerCase(): bool
    {
        return $this->strength['lowerCase'];
    }

    public function hasNumber(): bool
    {
        return $this->strength['number'];
    }

    public function hasSpecialChar(): bool
    {
        return $this->strength['specialChar'];
    }

    public function calculateStrength(): int
    {
        return array_sum($this->strength);
    }

    protected function inspectPassword(): void
    {
        if (strlen($this->password) >= $this->minLength) {
            $this->strength['minLength']++;
        }
        
        $tester = [
            'lowerCase' => '/[a-z]/',
            'upperCase' => '/[A-Z]/',
            'number' => '/[0-9]/',
            'specialChar' => '/[^a-zA-Z0-9]/'
        ];

        foreach($tester as $key => $regex) {
            if(preg_match($regex, $this->password)) {
                $this->strength[$key]++;
            }
        }
    }
}