<?php

use Ucscode\SQuery\SQuery;
use Ucscode\UssElement\UssElement;
use Ucscode\DOMTable\DOMTable;
use Ucscode\DOMTable\DOMTableInterface;
use Ud\bundle\DOMTableWidget;

class CrudIndexManager extends AbstractCrudIndexManager
{
    public function __construct(string $tablename)
    {
        parent::__construct($tablename);
        $this->configureProperties();
        $this->setDefaultBulkActions();
        $this->setDefaultItemActions();
    }

    public function createUI(?DOMTableWidget $fabricator = null): string
    {
        $this->mainContainer->appendChild($this->widgetContainer);
        $this->tableContainer->appendChild($this->domTable->getTableContainerElement());
        $this->mainContainer->appendChild($this->tableContainer);
        $this->mainContainer->appendChild($this->paginatorContainer);

        $this->buildBulkActionsElement();
        $this->buildDOMTable($fabricator);

        return $this->mainContainer->getHTML();
    }

    /**
     * @method configureProperties
     */
    protected function configureProperties(): void
    {
        $uss = Uss::instance();

        $tableColumns = array_map(function($value) {
            return str_replace('_', ' ', $value);
        }, $uss->getTableColumns($this->tablename));

        $this->setMultipleTableColumns($tableColumns);

        $divNodes = [
            'mainContainer' => 'container', 
            'widgetContainer' => 'widget', 
            'paginatorContainer' => 'paginator',
            'tableContainer' => 'table'
        ];

        foreach($divNodes as $key => $classname) {
            $this->{$key} = new UssElement(UssElement::NODE_DIV);
            $this->{$key}->setAttribute('class', 'crud-' . $classname);
        };

        $this->domTable = new DOMTable($this->tablename);
        $this->domTable->getTableElement()->addAttributeValue('class', 'table-striped');
        $this->sQuery = (new SQuery())->select('*', $this->tablename);
        $this->evalSQueryUpdate();
    }

    /**
     * @method configureBulkActions
     */
    public function setDefaultBulkActions(): void
    {
        $noAction = (new CrudAction())
            ->setLabel('- Select Action -')
            ->setValue('');
        
        $this->addBulkAction('no-action', $noAction);

        $deleteAction = (new CrudAction())
            ->setLabel('Delete')
            ->setValue('delete');
            
        $this->addBulkAction('delete', $deleteAction);
    }

    /**
     * @method configureItemActions
     */
    public function setDefaultItemActions(): void
    {
        $this->addItemAction('edit', new class() implements CrudActionInterface
        {
            public function forEachItem(array $data): CrudAction
            {
                $curdAction = new CrudAction();

                $curdAction
                    ->setLabel('Edit')
                    ->setIcon('bi bi-person')
                    ->setElementType(CrudAction::TYPE_BUTTON)
                    ->setValue('button')
                    ->setElementAttribute('href', 'clone')
                    ->setElementAttribute('volk', 'sponge');

                return $curdAction;
            }
        });

        $this->addItemAction('delete', new class() implements CrudActionInterface
        {
            public function forEachItem(array $data): CrudAction
            {
                return (new CrudAction())
                    ->setLabel('Delete')
                    ->setIcon('bi bi-trash')
                    ->setElementType(CrudAction::TYPE_ANCHOR)
                    ->setValue('https://example.com')
                    ->setElementAttribute('target', '_blank');
            }
        });
    }

    /**
     * @method buildDOMTable
     */
    public function buildDOMTable(?DOMTableInterface $fabricator): void
    {
        $this->domTable->setData($this->mysqliResult);

        $columns = $this->getTableColumns();

        if(!$this->hideBulkActions) {
            $columns = ['checkbox' => $this->checker()->getHTML()] + $columns;
        }

        if(!$this->hideItemActions) {
            $columns['actions'] = '';
        }

        $this->domTable->setMultipleColumns($columns);
        $this->domTable->setCurrentPage($this->paginator->getCurrentPage());
        $this->domTable->setDisplayFooter($this->displayTfoot);
        $this->domTable->setRowsPerPage($this->paginator->getItemsPerPage());
        
        $crudActionParser = new CrudItemIterator(
            $fabricator,
            $this,
            Closure::fromCallable([$this, 'checker']),
            $this->domTable
        );

        $this->domTable->build($crudActionParser);
    }

    /**
     * @method checker
     */
    protected function checker(?string $value = null): UssElement
    {
        $container = new UssElement(UssElement::NODE_DIV);
        $container->setAttribute('class', 'form-check');
        $checker = new UssElement(UssElement::NODE_INPUT);
        $checker->setAttribute('type', 'checkbox');
        $checker->setAttribute('class', 'form-check-input');
        if(!is_null($value)) {
            $checker->setAttribute('name', 'data[]');
            $checker->setAttribute('value', $value);
        }
        $container->appendChild($checker);
        return $container;
    }

    /**
     * @method buildBulkActions
     */
    protected function buildBulkActionsElement(): void
    {
        if(!$this->hideBulkActions) {

            $bulkActions = new UssElement(UssElement::NODE_DIV);
            $bulkActions->setAttribute('class', 'bulk-actions row my-1 py-1 border-bottom');

            $bulkActionColumn = new UssElement(UssElement::NODE_DIV);
            $bulkActionColumn->setAttribute('class', 'col-lg-5 col-md-7 ms-auto d-flex justify-content-end align-items-center');
            $bulkActions->appendChild($bulkActionColumn);

            $bulkSelect = new UssElement(UssElement::NODE_SELECT);
            $bulkSelect->setAttribute('class', 'form-select form-select-sm mb-1 mx-1');
            $bulkSelect->setAttribute('name', 'action');
            $bulkSelect->setAttribute('required', 'required');
            $bulkActionColumn->appendChild($bulkSelect);

            $bulkButton = new UssElement(UssElement::NODE_BUTTON);
            $bulkButton->setAttribute('class', 'btn btn-primary btn-sm text-nowrap');
            $bulkButton->setContent('Apply Action');
            $bulkActionColumn->appendChild($bulkButton);

            foreach($this->bulkActions as $crudAction) {

                $option = new UssElement(UssElement::NODE_OPTION);
                $option->setAttribute('value', $crudAction->getValue());
                $option->setContent($crudAction->getLabel());

                foreach($crudAction->getElementAttributes() as $key => $value) {
                    $option->setAttribute($key, $value);
                }

                $bulkSelect->appendChild($option);
            }

            $this->tableContainer->insertBefore(
                $bulkActions, 
                $this->domTable->getTableContainerElement()
            );
        }
    }

    /**
     * @method buildItemTools
     */
    protected function buildItemTools(): void
    {

    }
}
