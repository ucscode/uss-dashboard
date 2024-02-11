<?php

namespace Module\Dashboard\Bundle\User\Abstract;

use Module\Dashboard\Bundle\User\Interface\UserInterface;
use Module\Dashboard\Bundle\User\Service\Href;
use Module\Dashboard\Bundle\User\Service\Meta;
use Module\Dashboard\Bundle\User\Service\Notification;
use Module\Dashboard\Bundle\User\Service\Roles;
use Ucscode\Pairs\ForeignConstraint;
use Ucscode\Pairs\Pairs;
use Uss\Component\Kernel\Uss;
use Uss\Component\Manager\Entity;

abstract class AbstractUserFoundation implements UserInterface
{
    public readonly Meta $meta;
    public readonly Roles $roles;
    public readonly Notification $notification;
    public readonly Href $href;

    protected Entity $entity;
    private ?Pairs $pairsInstance = null;

    public function __construct(?int $id = null)
    {
        $this->configureUser();
        $this->entity = $this->obtainUserEntity($id);
        $this->meta = new Meta($this, $this->pairsInstance);
        $this->roles = new Roles($this);
        $this->notification = new Notification($this);
        $this->href = new Href($this);
    }

    /**
     * @see https://www.php.net/manual/en/language.oop5.magic.php#object.debuginfo
     */
    public function __debugInfo()
    {
        return [
            'user:protected' => $this->entity,
            'meta' => $this->objectize($this->meta::class),
            'roles' => $this->objectize($this->roles::class),
            'notification' => $this->objectize($this->notification::class),
            'href' => $this->objectize($this->href::class),
        ];
    }

    /**
     * @method acquireUser
     */
    protected function obtainUserEntity(?int $id, ?string $column = null): Entity|string|null
    {
        $user = !is_null($id) ? Uss::instance()->fetchItem(self::TABLE_USER, abs($id)) : null;
        $entity = new Entity(self::TABLE_META, $user ?? []);
        return $column === null ? $entity : $entity->get($column);
    }

    /**
     * @method init
     */
    private function configureUser(): void
    {
        if(!$this->pairsInstance) {
            $constraint = new ForeignConstraint(self::TABLE_USER);
            $constraint->describePrimaryKeyUnsigned(true);
            $this->pairsInstance = new Pairs(Uss::instance()->mysqli, self::TABLE_META);
            $this->pairsInstance->setForeignConstraint($constraint);
        }
    }

    private function objectize(string $className): string
    {
        return sprintf("object(%s) {}", $className);
    }
}
