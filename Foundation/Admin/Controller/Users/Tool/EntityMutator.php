<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Users\Tool;

use Module\Dashboard\Bundle\Crud\Component\CrudEnum;
use Module\Dashboard\Bundle\User\User;
use Ucscode\DOMTable\Interface\DOMTableIteratorInterface;
use Uss\Component\Kernel\Uss;

class EntityMutator implements DOMTableIteratorInterface
{
    protected User $client;
    protected string $nullValue = "<span class='text-muted'>NULL</span>";

    public function foreachItem(array $item): ?array
    {
        $this->client = new User($item['id']);
        $item['parent'] = $this->defineParent($item['parent']);
        $item['roles'] = $this->defineRoles();
        $item['register_time'] = $this->client->getRegisterTime()->format('j, M Y');
        $item['last_seen'] = Uss::instance()->relativeTime($item['last_seen']);
        return $item;
    }

    protected function defineParent(?string $parentId): string
    {
        if($parentId !== null) {
            $parent = new User($parentId);
            if($parent->isAvailable()) {
                return sprintf(
                    "<a href='%s'>%s</a>",
                    Uss::instance()->replaceUrlQuery([
                        'entity' => $parent->getId(),
                        'channel' => CrudEnum::UPDATE->value,
                    ]),
                    $parent->getUsername() ?: $parent->getEmail()
                );
            }
        }
        return $this->nullValue;
    }

    protected function defineRoles(): string
    {
        $roles = $this->client->roles->getAll();
        if($roles) {
            $implodedRoles = implode(", ", $roles);
            return sprintf(
                "<span class='text-truncate d-inline-block' style='max-width: 145px;'>%s</span>",
                $implodedRoles
            );
        }
        return $this->nullValue;
    }
}