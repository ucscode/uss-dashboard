<?php

namespace Module\Dashboard\Foundation\Bundle\User;

use Ucscode\Pairs\Pairs;
use Uss\Component\Kernel\Uss;

abstract class AbstractUserFoundation implements UserInterface
{
    protected array $user;
    protected ?string $error = null;
    protected static ?Pairs $usermeta = null;

    public function __construct(?int $id = null)
    {
        $this->init();
        $this->user = $this->getUser($id) ?? [];
    }

    /**
     * @see https://www.php.net/manual/en/language.oop5.magic.php#object.debuginfo
     */
    public function __debugInfo()
    {
        return [
            'user' => $this->user,
            'errors' => $this->error
        ];
    }

    /**
     * @method getMetaInstance
     */
    public static function getMetaInstance(): ?Pairs
    {
        return self::$usermeta;
    }

    /**
     * @method getError
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * @method allocate
     */
    public function allocate(string $key, string $value): self
    {
        if($this->exists()) {
            throw new \Exception(
                sprintf(
                    "%s::%s: Allocation is only possible if user does not already exist",
                    get_called_class(),
                    __FUNCTION__
                )
            );
        };
        $user = Uss::instance()->fetchItem(self::USER_TABLE, $value, $key);
        if($user) {
            $this->user = $user;
        }
        return $this;
    }

    /**
     * @method getUser
     */
    protected function getUser(?int $id): ?array
    {
        if($id) {
            $id = abs($id);
            return Uss::instance()->fetchItem(self::USER_TABLE, $id);
        }
        return null;
    }

    /**
     * @method init
     */
    protected function init(): void
    {
        if(!self::$usermeta) {
            self::$usermeta = new Pairs(Uss::instance()->mysqli, self::META_TABLE);
            self::$usermeta->linkToParentTable([
                'parentTable' => self::USER_TABLE,
            ]);
        }
    }
}
