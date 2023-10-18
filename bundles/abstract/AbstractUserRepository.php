<?php

use Ucscode\SQuery\SQuery;

abstract class AbstractUserRepository extends AbstractUserFoundation
{
    /**
     * @method getId
     */
    public function getId(): ?int
    {
        return $this->user['id'] ?? null;
    }

    /**
     * @method setEmail
     */
    public function setEmail(string $email): self
    {
        if(!filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
            throw new \Exception(
                sprintf(
                    "%s::%s: Invalid email argument",
                    get_called_class(),
                    __FUNCTION__
                )
            );
        };
        $this->user['email'] = strtolower(trim($email));
        return $this;
    }

    /**
     * @method getEmail
     */
    public function getEmail(): ?string
    {
        return $this->user['email'] ?? null;
    }

    /**
     * @method setUsername
     */
    public function setUsername(string $username): self
    {
        if(!preg_match('/^\w+$/i', trim($username))) {
            throw new \Exception(
                sprintf(
                    "%s::%s: Username can only contain letter, number and underscore",
                    get_called_class(),
                    __FUNCTION__
                )
            );
        }
        $this->user['username'] = strtolower(trim($username));
        return $this;
    }

    /**
     * @method getUsername
     */
    public function getUsername(): ?string
    {
        return $this->user['username'] ?? null;
    }

    /**
     * @method setPassword
     */
    public function setPassword(string $password, bool $passwordHash = false): self
    {
        if($passwordHash) {
            $password = password_hash($password, PASSWORD_DEFAULT);
        }
        $this->user['password'] = $password;
        return $this;
    }

    /**
     * @method getPassword
     */
    public function getPassword(): ?string 
    {
        return $this->user['password'] ?? null;
    }

    /**
     * Use this method only if password is hashed with PHP "password_hash" function
     * @method isValidPassword
     */
    public function passwordVerify(string $password): bool 
    {
        return password_verify($password, $this->getPassword() ?? '');
    }

    /**
     * @method setRegisterTime
     */
    public function setRegisterTime(\DateTime $dateTime): self
    {
        $registerTime = $dateTime->format('Y-m-d H:i:s');
        $this->user['register_time'] = $registerTime;
        return $this;
    }

    /**
     * @method getRegisterTime
     */
    public function getRegisterTime(): ?\DateTime
    {
        $registerTime = $this->user['register_time'] ?? null;
        if($registerTime) {
            $registerTime = new \DateTime($registerTime);
        }
        return $registerTime;
    }

    /**
     * @method setUsercode
     */
    public function setUsercode(string $usercode): self
    {   
        if(!preg_match('/^[a-z0-9]+$/i', $usercode)) {
            throw new \Exception(
                sprintf(
                    "%s::%s: Usercode cannot contain special character"
                )
            );
        }
        $this->user['usercode'] = $usercode;
        return $this;
    }

    /**
     * @method getUsercode
     */
    public function getUsercode(): ?string
    {
        return $this->user['usercode'] ?? null;
    }

    /**
     * @method setLastSeen
     */
    public function setLastSeen(\DateTime $dateTime): ?self
    {
        $lastSeen = $dateTime->format('Y-m-d H:i:s');
        $this->user['last_seen'] = $lastSeen;
        return $this;
    }

    /**
     * @method getLastSeen
     */
    public function getLastSeen(): ?\DateTime
    {
        $lastSeen = $this->user['last_seen'] ?? null;
        if($lastSeen) {
            $lastSeen = new \DateTime($lastSeen);
        };
        return $lastSeen;
    }

    /**
     * @method setParent
     */
    public function setParent(User|int|null $parent): self
    {
        if($parent instanceof User) {
            $parent = $parent->getId();
        };
        $this->user['parent'] = $parent;
        return $this;
    }

    /**
     * @method getParent
     */
    public function getParent(bool $getUserInstance = false): User|int|null 
    {
        $parent = $this->user['parent'] ?? null;
        if($parent && $getUserInstance) {
            $parent = new User($parent);
            if(!$parent->getId()) {
                $parent = null;
            }
        };
        return $parent;
    }

    /**
     * @method getChildren
     */
    public function getChildren(?callable $filter = null): array
    {
        $children = [];

        $SQL = (new SQuery())
            ->select('*')
            ->from(self::USER_TABLE)
            ->where('parent', $this->getId() ?? -1);
        
        if(is_callable($filter)) {
            $SQL = call_user_func($filter, $SQL);
            if(!($SQL instanceof SQuery)) {
                throw new \Exception(
                    sprintf(
                        "%s::%s: argument must return an instance of SQuery",
                        get_called_class(),
                        __FUNCTION__
                    )
                );
            }
        }

        $result = Uss::instance()->mysqli->query($SQL);

        if($result->num_rows) {
            while($child = $result->fetch_assoc()) {
                $children[] = new User($child['id']);
            }
        };

        return $children;
    }

    /**
     * @method delete
     */
    public function delete(): ?bool
    {
        if($this->getId()) {
            $SQL = (new SQuery())->delete()
                ->from(self::USER_TABLE)
                ->where('id', $this->getId());
            return Uss::instance()->mysqli->query($SQL);
        };
        return null;
    }

    /**
     * @method persist
     */
    public function persist(): bool
    {
        $uss = Uss::instance();
        if(!$this->exists()) {
            $SQL = (new SQuery())
                ->insert(self::USER_TABLE, $this->user);
            $insert = $uss->mysqli->query($SQL);
            if($insert) {
                $userid = $uss->mysqli->insert_id;
                $this->user = $this->getUser($userid);
            };
        } else {
            $SQL = (new SQuery())
                ->update(self::USER_TABLE, $this->user)
                ->where('id', $this->getId());
            $insert = $uss->mysqli->query($SQL);
        }
        return $insert;
    }

    /**
     * @method exists
     */
    public function exists(): bool
    {
        return !!$this->getId();
    }

    /**
     * @method saveToSession
     */
    public function saveToSession()
    {
        if($this->exists()) {
            $userSecret = $this->getPassword(). $this->getUsercode();
            $var = $this->getId(). ":" . hash('sha256', $userSecret);
            $_SESSION[self::SESSION_KEY] = $var;
        }
    }

    /**
     * @method getFromSession
     */
    public function getFromSession(): ?User
    {
        if(!$this->exists()) {
            $session_value = $_SESSION[self::SESSION_KEY] ?? null;
            
            if(!empty($session_value) && is_string($session_value)) {
                $detail = explode(":", $session_value);

                if(count($detail) === 2 && is_numeric($detail[0])) {
                    $user = $this->getUser($detail[0]);

                    if($user) {
                        $userSecret = $user['password'] . $user['usercode'];
                        $hash = hash('sha256', $userSecret);
                        
                        if($hash === $detail[1]) {
                            $this->user = $user;
                            return $this;
                        }
                    };

                };

            }
        };
        return null;
    }
}
