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

    public function getMinLength(): int
    {
        return $this->minLength;
    }

    public function hasMinLength(): bool
    {
        return !!$this->strength['minLength'];
    }

    public function hasUpperCase(): bool
    {
        return !!$this->strength['upperCase'];
    }

    public function hasLowerCase(): bool
    {
        return !!$this->strength['lowerCase'];
    }

    public function hasNumber(): bool
    {
        return !!$this->strength['number'];
    }

    public function hasSpecialChar(): bool
    {
        return !!$this->strength['specialChar'];
    }

    public function calculateStrength(): int
    {
        return array_sum($this->strength);
    }

    public function getHash(): ?string
    {
        return $this->password !== null ? 
            password_hash($this->password, PASSWORD_DEFAULT) : null;
    }

    public function verifyHash(?string $hash): bool
    {
        return 
            $this->password !== null && $hash !== null ?
            password_verify($this->password, $hash) : false;
    }

    public function getInput(): ?string
    {
        return $this->password;
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