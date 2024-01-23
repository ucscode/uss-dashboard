<?php

namespace Module\Dashboard\Bundle\User;

use Module\Dashboard\Bundle\User\Abstract\AbstractUserRepository;
use Uss\Component\Kernel\Uss;
use Ucscode\SQuery\SQuery;
use Ucscode\SQuery\Condition;

class User extends AbstractUserRepository
{
    /**
     * This signifies that user exists and has been populated into the current instance
     */
    public function isAvailable(): bool
    {
        return !!$this->getId();
    }

    /**
     * Get the user raw information
     */
    public function getRawInfo(): array
    {
        return $this->user;
    }

    /**
     * @method saveToSession
     */
    public function saveToSession(): self
    {
        if($this->isAvailable()) {
            $secret = $this->getPassword() . $this->getUsercode();
            $sessionValue = $this->getId(). ":" . hash('sha256', $secret);
            $_SESSION[self::SESSION_KEY] = $sessionValue;
        }
        return $this;
    }

    /**
     * @method getFromSession
     */
    public function acquireFromSession(): self
    {
        $sessionValue = $_SESSION[self::SESSION_KEY] ?? null;
        if(!$this->isAvailable() && !empty($sessionValue) && is_string($sessionValue)) {
            $detail = explode(":", $sessionValue);
            if(count($detail) === 2 && is_numeric($detail[0])) {
                $user = $this->acquireUser($detail[0]);
                if($user && hash('sha256', $user['password'] . $user['usercode']) === $detail[1]) {
                    $this->user = $user;
                };
            };
        }
        return $this;
    }

    /**
     * @method destroySession
     */
    public function destroySession(): self
    {
        if(isset($_SESSION[self::SESSION_KEY])) {
            unset($_SESSION[self::SESSION_KEY]);
        };
        return $this;
    }

    /**
    * @method persist
    */
    public function persist(): bool
    {
        $uss = Uss::instance();

        if(!$this->isAvailable()) {

            $squery = (new SQuery())->insert(self::USER_TABLE, $this->user);
            $SQL = $squery->build();
            $upsert = $uss->mysqli->query($SQL);

            if($upsert) {
                $userId = $uss->mysqli->insert_id;
                $this->user = $this->acquireUser($userId);
            };

        } else {

            $squery = (new SQuery())
                ->update(self::USER_TABLE, $this->user)
                ->where(
                    (new Condition())
                        ->add('id', $this->getId())
                );

            $SQL = $squery->build();
            $upsert = $uss->mysqli->query($SQL);

        }

        return $upsert;
    }

    /**
     * @method allocate
     */
    public function allocate(string $key, string $value): self
    {
        if($this->isAvailable()) {
            throw new \Exception(
                "Allocation is only possible if user does not already exist"
            );
        };
        
        if($user = Uss::instance()->fetchItem(self::USER_TABLE, $value, $key)) {
            $this->user = $user;
        }

        return $this;
    }

    /**
     * @method delete
     */
    public function delete(): ?bool
    {
        if($this->getId()) {
            $squery = (new SQuery())
                ->delete()
                ->from(self::USER_TABLE)
                ->where(
                    (new Condition())
                        ->add('id', $this->getId())
                );
            $SQL = $squery->build();
            return Uss::instance()->mysqli->query($SQL);
        };
        return null;
    }
}
