<?php

namespace Module\Dashboard\Foundation\User\Controller;

use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;
use Module\Dashboard\Bundle\Common\Document;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormInterface;
use Uss\Component\Block\Block;
use Uss\Component\Block\BlockManager;

class PasswordController extends ProfileController
{
    public function composeApplication(DashboardInterface $dashboard, Document $document, ?DashboardFormInterface $form): void
    {
        /**
         * You may be required to add content or template to the below block after `modules:loaded` event
         * The block may not be available before your module load.
         * A better work around would be to use the nullsafe operator "?->".
         * 
         * BlockManager::instance()->getBlock('password_space')?->addTemplate(...)
         */
        $block = new Block();
        BlockManager::instance()->addBlock("password_space", new Block());
        parent::composeApplication($dashboard, $document, $form);
    }
}
