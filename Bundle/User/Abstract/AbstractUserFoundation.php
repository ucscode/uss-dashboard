<?php

namespace Module\Dashboard\Bundle\User\Abstract;

use Module\Dashboard\Bundle\User\Interface\UserInterface;
use Module\Dashboard\Bundle\User\Service\Mailer;
use Module\Dashboard\Bundle\User\Service\Meta;
use Module\Dashboard\Bundle\User\Service\Notification;
use Module\Dashboard\Bundle\User\Service\Roles;
use Ucscode\Pairs\ForeignConstraint;
use Ucscode\Pairs\Pairs;
use Uss\Component\Kernel\Uss;

abstract class AbstractUserFoundation implements UserInterface
{
    protected array $user;
    public readonly Meta $meta;
    public readonly Roles $roles;
    public readonly Mailer $mailer;
    public readonly Notification $notification;
    private static ?Pairs $usermeta = null;

    public function __construct(?int $id = null)
    {
        $this->syncOnce();
        $this->meta = new Meta($this, self::$usermeta);
        $this->roles = new Roles($this);
        $this->mailer = new Mailer($this);
        $this->notification = new Notification($this);
        $this->user = $this->acquireUser($id) ?? [];
    }

    /**
     * @see https://www.php.net/manual/en/language.oop5.magic.php#object.debuginfo
     */
    public function __debugInfo()
    {
        return [
            'user' => $this->user,
            'meta' => $this->meta->getAll()
        ];
    }

    /**
     * @method acquireUser
     */
    protected function acquireUser(?int $id): ?array
    {
        return $id ? Uss::instance()->fetchItem(self::USER_TABLE, abs($id)) : null;
    }

    /**
     * @method init
     */
    protected function syncOnce(): void
    {
        if(!self::$usermeta) {
            self::$usermeta = new Pairs(Uss::instance()->mysqli, self::META_TABLE);
            $constraint = (new ForeignConstraint(self::USER_TABLE))
                ->describePrimaryKeyUnsigned(true);
            self::$usermeta->setForeignConstraint($constraint);
        }
    }
}
