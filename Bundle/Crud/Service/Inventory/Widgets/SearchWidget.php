<?php

namespace Module\Dashboard\Bundle\Crud\Service\Inventory\Widgets;

use Module\Dashboard\Bundle\Crud\Kernel\Interface\CrudKernelInterface;
use Module\Dashboard\Bundle\Crud\Kernel\Interface\CrudWidgetInterface;
use Ucscode\UssElement\UssElement;
use Ucscode\UssForm\Field\Field;
use Ucscode\UssForm\Form\Form;
use Uss\Component\Block\BlockTemplate;

class SearchWidget implements CrudWidgetInterface
{
    protected Form $form;
    protected Field $field;
    protected UssElement $submitButton;
    
    public function __construct()
    {
        $this->prepareResources();
    }

    public function createWidget(CrudKernelInterface $crudKernel): BlockTemplate
    {
        $blockTemplate = new BlockTemplate(
            '@Foundation/System/Template/widget.html.twig',
            $this->getContext()
        );
        return $blockTemplate;
    }

    protected function getContext(): array
    {
        return [
            'widgetContentClass' => 'col-lg-6',
            'widgetContent' => $this->form->export(),
            'widgetName' => 'search',
        ];
    }

    protected function prepareResources(): void
    {
        $this->form = new Form();
        $this->form->getAttribute()->setMethod('get');
        $searchButton = $this->createSearchButton();
        $this->createSearchInput($searchButton);
    }

    protected function createSearchButton(): UssElement
    {
        $searchButton = new UssElement(UssElement::NODE_BUTTON);
        $searchButton
            ->setAttribute('class', 'btn btn-primary')
            ->setContent("<i class='bi bi-search me-1'></i> Search");
        return $searchButton;
    }

    protected function createSearchInput(UssElement $searchButton): void
    {
        $field = new Field(Field::NODE_INPUT, Field::TYPE_SEARCH);

        $collection = $this->form->getCollection(Form::DEFAULT_COLLECTION);
        $collection->addField("search", $field);
        
        $fieldContext = $field->getElementContext();
        $fieldContext->label->setDOMHidden(true);
        $fieldContext->suffix->setValue($searchButton);
        $fieldContext->container->addClass('input-group-sm');

        $fieldContext->widget
            ->setRequired(false)
            ->addClass('form-control-sm')
            ->setAttribute('placeholder', 'Type here...')
            ->setValue($_GET['search'] ?? null)
        ;
    }
}