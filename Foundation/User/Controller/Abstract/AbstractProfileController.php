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
    protected function GUIBuilder(DashboardInterface $dashboard, Document $document, ?DashboardFormInterface $form): void
    {
        $layout = '@Foundation/User/Template/profile/layout.html.twig';
        $content = $document->getTemplate();

        BlockManager::instance()->getBlock('profile_content')
            ->addTemplate("resource", new BlockTemplate($content));

        $document->setTemplate($layout);
        
        $this->composeApplication($dashboard, $document, $form);
    }
}
