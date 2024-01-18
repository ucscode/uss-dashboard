<?php

namespace Module\Dashboard\Foundation\Admin\Controller;

use Module\Dashboard\Bundle\Common\Document;
use Module\Dashboard\Bundle\Crud\Service\Inventory\CrudInventory;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardController;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormInterface;
use Module\Dashboard\Bundle\User\Interface\UserInterface;
use Ucscode\DOMTable\Interface\DOMTableIteratorInterface;
use Ucscode\UssElement\UssElement;

class UsersController extends AbstractDashboardController
{
    public function composeApplication(DashboardInterface $dashboard, Document $document, ?DashboardFormInterface $form): void
    {
        $inventory = new CrudInventory(UserInterface::USER_TABLE);
        $inventory->setColumn("postGres");
        $inventory->setItemsMutationIterator(new class implements DOMTableIteratorInterface {
            public function foreachItem(array $item): ?array
            {
                $div = new UssElement(UssElement::NODE_DIV);
                $span = new UssElement(UssElement::NODE_SPAN);
                $span->setContent("Spider Man");
                $span2 = new UssElement(UssElement::NODE_SPAN);
                $span2->setContent("WAiTer-" . $item['id']);
                $div->appendChild($span);
                $div->appendChild($span2);
                $item['postGres'] = $div;
                return $item;
            }
        });
        $inventory->setTableBackgroundWhite();
        $inventory->setInlineActionAsDropdown(false);
        $inventory->setInlineActionAsDropdown(true);
        $document->setContext([
            'inventory' => $inventory,
        ]);
    }
}
