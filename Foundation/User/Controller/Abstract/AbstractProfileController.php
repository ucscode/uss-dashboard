<?php

namespace Module\Dashboard\Foundation\User\Controller\Abstract;

use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardController;
use Uss\Component\Block\BlockManager;
use Uss\Component\Block\BlockTemplate;

abstract class AbstractProfileController extends AbstractDashboardController
{
    public function onload(array $context): void
    {
        parent::onload($context);

        BlockManager::instance()
            ->getBlock('profile_content')
            ->addTemplate("resource", new BlockTemplate($this->document->getTemplate()));

        $layout = '@Foundation/User/Template/profile/layout.html.twig';
        
        $this->document->setTemplate($layout);
    }
}
