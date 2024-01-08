<?php

namespace Module\Dashboard\Foundation\User\Controller\Abstract;

use Module\Dashboard\Bundle\Common\Document;
use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardController;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;
use Module\Dashboard\Bundle\Kernel\Interface\DashboardFormInterface;
use Uss\Component\Block\BlockManager;
use Uss\Component\Block\BlockTemplate;

abstract class AbstractProfileController extends AbstractDashboardController
{
    protected function composeApplication(DashboardInterface $dashboard, Document $document, ?DashboardFormInterface $form): void
    {
        BlockManager::instance()->getBlock('profile_content')
            ->addTemplate(
                "profile_content",
                new BlockTemplate($document->getTemplate())
            );
        $layout = '@Foundation/User/Template/profile/layout.html.twig';
        /**
         * Render works only after all modules have been loaded
         * Therefore, you can call this method on the child class.
         * ```
         * Parent::composeApplication(...)
         * ```
         * And handle your code while still within PHP environment
         */
        $dashboard->render($layout, $document->getContext());
    }
}