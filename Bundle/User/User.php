<?php

namespace Module\Dashboard\Bundle\User;

use Module\Dashboard\Bundle\User\Abstract\AbstractUserRepository;
use Module\Dashboard\Bundle\User\Service\Href;
use Uss\Component\Kernel\Uss;
use Ucscode\SQuery\SQuery;
use Ucscode\SQuery\Condition;
use Uss\Component\Common\Entity;

class User extends AbstractUserRepository
{
    /**
     * The user exists in database and has been populated into the current instance
     */
    public function isAvailable(): bool
    {
        $userId = $this->getId(); // get assigned
        $entity = $this->obtainUserEntity($userId); // Fetch from database
        return !empty($entity->get('id')); // check existence
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
                $entity = $this->obtainUserEntity($detail[0]);
                $approved = $entity->get('id') && hash('sha256', $entity->get('password') . $entity->get('usercode')) === $detail[1];
                !$approved ?: $this->entity = $entity;
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

            $SQL = (new SQuery())
                ->insert(self::TABLE_USER, $this->entity->getAll())
                ->build();

            $upsert = $uss->mysqli->query($SQL);

            if($upsert) {
                $userId = $uss->mysqli->insert_id;
                $this->entity = $this->obtainUserEntity($userId);
            };

        } else {

            $condition = (new Condition())->add('id', $this->getId());

            $SQL = (new SQuery())
                ->update(self::TABLE_USER, $this->entity->getAll())
                ->where($condition)
                ->build()
            ;

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
            $allocationError = "Allocation is only possible if user does not already exist";
            throw new \Exception($allocationError);
        };

        $user = Uss::instance()->fetchItem(self::TABLE_USER, $value, $key);
        !$user ?: $this->entity = new Entity(self::TABLE_USER, $user);

        return $this;
    }

    /**
     * @method delete
     */
    public function delete(): ?bool
    {
        if($this->isAvailable()) {
            $condition = (new Condition())->add('id', $this->getId());

            $SQL = (new SQuery())
                ->delete()
                ->from(self::TABLE_USER)
                ->where($condition)
                ->build();

            return Uss::instance()->mysqli->query($SQL);
        };
        return null;
    }

    /**
     * @method isLonely
     * 
     * Check if current user is the only available user in database
     */
    public function isLonely(): bool
    {
        if($this->isAvailable()) {
            $SQL = (new SQuery())
                ->select("COUNT(id) AS totalUsers")
                ->from(self::TABLE_USER)
                ->build();

            $result = Uss::instance()->mysqli->query($SQL);
            $assoc = $result->fetch_assoc();
            
            return $assoc && (int)$assoc['totalUsers'] === 1;
        }
        return false;
    }

    /**
     * @method getParentByReferralLink
     */
    public function setParentByReferralLink(): bool
    {
        $parentCode = $_GET[Href::REFERRAL_LINK_OFFSET] ?? null;
        if(!empty($parentCode)) {
            $parent = new Self();
            $parent->allocate('usercode', $parentCode);
            $approved = 
                $parent->isAvailable() && 
                $this->getId() !== $parent->getId() && 
                $this->getUsercode() !== $parent->getUsercode()
            ;
            return $approved ? !!$this->setParent($parent) : false;
        }
        return false;
    }
}