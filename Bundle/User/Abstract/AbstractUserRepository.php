<?php

namespace Module\Dashboard\Bundle\User\Abstract;

use DateTime;
use Exception;
use InvalidArgumentException;
use Module\Dashboard\Bundle\Common\Password;
use Ucscode\SQuery\Condition;
use Ucscode\SQuery\SQuery;
use Module\Dashboard\Bundle\Immutable\DashboardImmutable;
use Module\Dashboard\Bundle\User\User;
use Uss\Component\Kernel\Uss;

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
            throw new InvalidArgumentException(
                "The provided email address is invalid."
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
    public function setUsername(?string $username): self
    {
        if($username !== null) {
            if(!preg_match('/^\w[a-z0-9_\-]+$/i', trim($username))) {
                throw new InvalidArgumentException(
                    "Username can only contain letter, number, underscore and (but must not start with) hyphen"
                );
            }
        }
        $this->user['username'] = $username !== null ? strtolower(trim($username)) : $username;
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
    public function setPassword(string|Password $context, bool $hash = false): self
    {
        $context = is_string($context) ? new Password($context) : $context;
        $password = $hash ? $context->getHash() : $context->getInput();
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
     *
     * @method validatePassword
     */
    public function verifyPassword(string|Password $context): bool
    {
        $context = is_string($context) ? new Password($context) : $context;
        return $context->verifyHash($this->getPassword());
    }

    /**
     * @method setRegisterTime
     */
    public function setRegisterTime(DateTime $dateTime): self
    {
        $registerTime = $dateTime->format('Y-m-d H:i:s');
        $this->user['register_time'] = $registerTime;
        return $this;
    }

    /**
     * @method getRegisterTime
     */
    public function getRegisterTime(): ?DateTime
    {
        $registerTime = $this->user['register_time'] ?? null;
        if($registerTime) {
            $registerTime = new DateTime($registerTime);
        }
        return $registerTime;
    }

    /**
     * @method setUsercode
     */
    public function setUsercode(string $usercode): self
    {
        if(!preg_match('/^[a-z0-9]+$/i', $usercode)) {
            throw new InvalidArgumentException(
                "Usercode cannot contain special character"
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
    public function setLastSeen(DateTime $dateTime): ?self
    {
        $lastSeen = $dateTime->format('Y-m-d H:i:s');
        $this->user['last_seen'] = $lastSeen;
        return $this;
    }

    /**
     * @method getLastSeen
     */
    public function getLastSeen(): ?DateTime
    {
        $lastSeen = $this->user['last_seen'] ?? null;
        if($lastSeen) {
            $lastSeen = new DateTime($lastSeen);
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
            if(!$parent->isAvailable()) {
                $parent = null;
            }
        };
        return $parent;
    }

    /**
     * @method hasParent
     */
    public function hasParent(): bool
    {
        return $this->getParent() !== null;
    }

    /**
     * @method getChildren
     */
    public function getChildren(?Condition $filter = null): array
    {
        $children = [];

        if($this->isAvailable()) {
            $filter ??= new Condition();
            $filter->add('parent', $this->getId());

            $squery = (new SQuery())
                ->select('*')
                ->from(self::USER_TABLE)
                ->where($filter);

            $SQL = $squery->build();
            $result = Uss::instance()->mysqli->query($SQL);

            if($result->num_rows) {
                while($child = $result->fetch_assoc()) {
                    $children[] = new User($child['id']);
                }
            };
        }

        return $children;
    }

    /**
     * @method childrenCount
     */
    public function childrenCount(): int
    {
        if($this->isAvailable()) {
            $filter = (new Condition())->add('parent', $this->getId());
            $SQL = (new SQuery())
                ->select("COUNT(id) as children")
                ->from(self::USER_TABLE)
                ->where($filter)
                ->build();
            $result = Uss::instance()->mysqli->query($SQL);
            $assoc = $result->fetch_assoc();
            if($assoc !== null) {
                return $assoc['children'];
            }
        }
        return 0;
    }

    /**
     * @method hasChildren
     */
    public function hasChildren(): bool
    {
        return !empty($this->childrenCount());
    }

    /**
     * @method getAvatar
     */
    public function getAvatar(): ?string
    {
        $default = Uss::instance()->pathToUrl(DashboardImmutable::ASSETS_DIR . "/images/user.png");
        $avatar = $this->meta->get('user.avatar');
        return $avatar ?? $default;
    }
}
