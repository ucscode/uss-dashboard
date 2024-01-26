<?php

namespace Module\Dashboard\Foundation\Admin\Controller\Settings\Abstract;

use Module\Dashboard\Bundle\Kernel\Abstract\AbstractDashboardController;
use Uss\Component\Block\BlockManager;
use Uss\Component\Block\BlockTemplate;

abstract class AbstractSettingsController extends AbstractDashboardController
{
    public function onload(array $context): void
    {
        parent::onload($context);

        BlockManager::instance()
            ->getBlock('settings_content')
            ->addTemplate('content', (new BlockTemplate($this->document->getTemplate())));

        $layout = '@Foundation\Admin\Template\settings\fragment\layout.html.twig';
        
        $this->document->setTemplate($layout);
    }
}