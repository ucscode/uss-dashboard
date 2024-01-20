<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Widgets\GlobalAction;

use Module\Dashboard\Bundle\Crud\Kernel\Interface\CrudKernelInterface;
use Module\Dashboard\Bundle\Crud\Kernel\Interface\CrudWidgetInterface;
use Ucscode\DOMTable\Interface\DOMTableIteratorInterface;
use Uss\Component\Block\BlockTemplate;

class GlobalActionsWidget extends AbstractGlobalActionsWidget implements CrudWidgetInterface
{
    public function createWidget(CrudKernelInterface $crudKernel): BlockTemplate
    {
        $this->crudInventory = $crudKernel;
        $this->configureAssociateTable();
        $this->createFormComponents();
        $this->applyInlineCheckbox();

        return new BlockTemplate(
            '@Foundation/System/Template/widget.html.twig',
            $this->getContext()
        );
    }

    protected function getContext(): array
    {
        return [
            'widgetName' => 'global-action',
            'widgetContent' => $this->form->export(),
        ];
    }
}