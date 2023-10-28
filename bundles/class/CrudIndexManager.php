<?php

use Ucscode\SQuery\SQuery;
use Ucscode\UssElement\UssElement;
use Ucscode\DOMTable\DOMTable;
use Ucscode\DOMTable\DOMTableInterface;
use Ucscode\UssForm\UssForm;

class CrudIndexManager extends AbstractCrudIndexManager
{
    public function __construct(string $tablename)
    {
        parent::__construct($tablename);
        $this->configureProperties();
        $this->setDefaultBulkActions();
        $this->setDefaultItemActions();
        $this->setDefaultWidgets();
    }

    public function createUI(?DOMTableInterface $fabricator = null): UssElement
    {
        $this->combineWidgetElements();

        $this->tableContainer->appendChild(
            $this->buildTableElements()
        );

        $this->mainContainer->appendChild($this->tableContainer);

        $this->buildDOMTable($fabricator);

        return $this->mainContainer;
    }

    /**
     * @method configureProperties
     */
    protected function configureProperties(): void
    {
        $tableColumns = Uss::instance()->getTableColumns($this->tablename);

        $tableColumns = array_map(function ($value) {
            return str_replace('_', ' ', $value);
        }, $tableColumns);

        $this->setMultipleTableColumns($tableColumns);

        $divNodes = [
            'mainContainer' => 'crud-container',
            'widgetContainer' => 'crud-widget row',
            'paginatorContainer' => 'crud-paginator',
            'tableContainer' => 'crud-table border-top border-bottom my-2 py-2'
        ];

        foreach($divNodes as $key => $classname) {
            $this->{$key} = new UssElement(UssElement::NODE_DIV);
            $this->{$key}->setAttribute('class', $classname);
        };

        $this->tableForm = new UssForm(
            $this->tablename . '-crud-form',
            $_SERVER['REQUEST_URI'],
            'POST'
        );

        $this->domTable = new DOMTable($this->tablename);
        $this->domTable->getTableElement()->addAttributeValue('class', 'table-striped');

        $currentPage = $_GET[self::PAGE_INDEX_KEY] ?? null;
        $currentPage = is_numeric($currentPage) ? abs($currentPage) : 1;
        $this->setCurrentPage($currentPage);

        $this->sQuery = (new SQuery())->select('*', $this->tablename);
        $this->mysqliResult = Uss::instance()->mysqli->query($this->sQuery);
    }

    /**
     * @method configureBulkActions
     */
    protected function setDefaultBulkActions(): void
    {
        $noAction = (new CrudAction())
            ->setLabel('- Select Action -')
            ->setElementAttribute('value', '');

        $this->addBulkAction('no-action', $noAction);

        $deleteAction = (new CrudAction())
            ->setLabel('Delete')
            ->setElementAttribute('value', 'delete');

        $this->addBulkAction('delete', $deleteAction);
    }

    /**
     * @method configureItemActions
     */
    protected function setDefaultItemActions(): void
    {
        $this->addItemAction('edit', new class () implements CrudActionInterface {
            public function forEachItem(array $item): CrudAction
            {
                $curdAction = new CrudAction();

                $curdAction
                    ->setLabel('Edit')
                    ->setIcon('bi bi-person')
                    ->setElementType(CrudAction::TYPE_BUTTON)
                    ->setElementAttribute('type', 'button');

                return $curdAction;
            }
        });

        $this->addItemAction('delete', new class ($this) implements CrudActionInterface {
            public function __construct(
                private CrudIndexManager $crudIndexManager
            ) {
            }

            public function forEachItem(array $item): CrudAction
            {
                $crudAction = (new CrudAction())
                    ->setLabel('Delete')
                    ->setIcon('bi bi-trash')
                    ->setElementType(CrudAction::TYPE_ANCHOR)
                    ->setElementAttribute('href', 'https://example.com')
                    ->setElementAttribute('target', '_blank');

                if($this->crudIndexManager->isDisplayItemActionsAsButton()) {
                    $crudAction->setElementAttribute('class', 'btn btn-outline-danger btn-sm text-nowrap');
                }

                return $crudAction;
            }
        });
    }

    /**
     * @method setDefaultWidgets
     */
    protected function setDefaultWidgets(): void
    {
        $searchContainer = $this->createSearchWidget();
        $this->addWidget('search', $searchContainer);

        $newContainer = $this->createAddNewWidget();
        $this->addWidget('add-new', $newContainer);
    }

    /**
     * @method buildDOMTable
     */
    protected function buildDOMTable(?DOMTableInterface $fabricator): void
    {
        $columns = $this->getTableColumns();

        if(!$this->hideBulkActions) {
            $checker = $this->checker(null, function ($input) {
                $input->setAttribute('data-select', 'multiple');
            });
            $columns = ['checkbox' => $checker] + $columns;
        }

        if($this->hideItemActions === false) {
            $columns['actions'] = '';
        }

        $this->domTable->setMultipleColumns($columns);
        $this->domTable->setDisplayFooter($this->displayTfoot);

        $crudActionParser = new CrudItemIterator(
            $fabricator,
            $this,
            Closure::fromCallable([$this, 'checker']),
            $this->domTable
        );

        $this->domTable->setData($this->mysqliResult, $crudActionParser);
        $this->domTable->build();

        $this->buildPaginatorElement();
    }

    /**
     * @method checker
     */
    protected function checker(?string $value = null, ?callable $caller = null): UssElement
    {
        $container = new UssElement(UssElement::NODE_DIV);
        $container->setAttribute('class', 'form-check');
        $checker = new UssElement(UssElement::NODE_INPUT);
        $checker->setAttribute('type', 'checkbox');
        $checker->setAttribute('class', 'form-check-input');
        if(!is_null($value)) {
            $checker->setAttribute('name', 'crud[data][]');
            $checker->setAttribute('value', $value);
        }
        if($caller) {
            call_user_func($caller, $checker);
        }
        $container->appendChild($checker);
        return $container;
    }

    /**
     * @method buildTableElements
     */
    public function buildTableElements(): UssElement
    {
        $tableContainer = $this->domTable->getTableContainerElement();
        if(!$this->hideBulkActions) {
            $bulkActionContainer = $this->buildBulkActionsElement();
            $this->tableForm->appendChild($bulkActionContainer);
            $this->tableForm->appendChild($tableContainer);
            return $this->tableForm;
        }
        return $tableContainer;
    }

    /**
     * @method buildBulkActions
     */
    protected function buildBulkActionsElement(): UssElement
    {
        $bulkActionContainer = new UssElement(UssElement::NODE_DIV);
        $bulkActionContainer->setAttribute('class', 'bulk-actions row my-1 py-1');

        $bulkActionColumn = new UssElement(UssElement::NODE_DIV);
        $bulkActionColumn->setAttribute('class', 'col-lg-5 col-md-7 ms-auto d-flex justify-content-end align-items-center');
        $bulkActionContainer->appendChild($bulkActionColumn);

        $bulkSelect = new UssElement(UssElement::NODE_SELECT);
        $bulkSelect->setAttribute('class', 'form-select form-select-sm mb-1 mx-1');
        $bulkSelect->setAttribute('name', 'crud[action]');
        $bulkSelect->setAttribute('required', 'required');
        $bulkActionColumn->appendChild($bulkSelect);

        $bulkButton = new UssElement(UssElement::NODE_BUTTON);
        $bulkButton->setAttribute('class', 'btn btn-primary btn-sm text-nowrap');
        $bulkButton->setContent('Apply Action');
        $bulkActionColumn->appendChild($bulkButton);

        foreach($this->bulkActions as $crudAction) {

            $option = new UssElement(UssElement::NODE_OPTION);
            $option->setAttribute('value', $crudAction->getElementAttribute('value'));
            $option->setContent($crudAction->getLabel());

            foreach($crudAction->getElementAttributes() as $key => $value) {
                $option->setAttribute($key, $value);
            }

            $bulkSelect->appendChild($option);
        }

        return $bulkActionContainer;
    }

    /**
     * @method createSearchWidget
     */
    protected function createSearchWidget(): UssElement
    {
        $searchContainer = new UssElement(UssElement::NODE_DIV);
        $searchContainer->setAttribute('class', 'col-lg-6 mb-1');

        $form = new UssForm(
            'search',
            parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
        );

        $form->add(
            'search',
            UssForm::NODE_INPUT,
            UssForm::TYPE_SEARCH,
            [
                'value' => $_GET['search'] ?? null,
                'label_class' => 'd-none',
                'column' => 'col-12 mb-0',
                'group' => [
                    'append' => (new UssElement('button'))
                        ->setAttribute('class', 'btn btn-sm btn-outline-secondary')
                        ->setAttribute('type', 'submit')
                        ->setContent('<i class="bi bi-search me-1"></i> search')
                ],
                'class' => 'form-control form-control-sm',
                'attr' => [
                    'placeholder' => 'Search'
                ]
            ]
        );

        $searchContainer->appendChild($form);
        return $searchContainer;
    }

    /**
     * @method combineWidgetElements
     */
    protected function combineWidgetElements(): void
    {
        if(!empty($this->widgets) && !$this->hideWidgets) {
            foreach($this->widgets as $widget) {
                $this->widgetContainer->appendChild($widget);
            }
            $this->mainContainer->appendChild($this->widgetContainer);
        }
    }

    /**
     * @method createAddNewWidget
     */
    protected function createAddNewWidget(): UssElement
    {
        $uss = Uss::instance();
        $path = $uss->filterContext($uss->splitUri());
        $href = new UrlGenerator($path, [
            'action' => 'edit'
        ]);

        $newContainer = new UssElement(UssElement::NODE_DIV);
        $newContainer->setAttribute('class', 'col-lg-6 ms-auto text-lg-end');

        $anchor = new UssElement(UssElement::NODE_A);
        $anchor->setAttribute('class', 'btn btn-success btn-sm');
        $anchor->setContent('<i class="bi bi-plus-circle-dotted me-1"></i>Add New');
        $anchor->setAttribute('href', $href);
        
        $newContainer->appendChild($anchor);

        return $newContainer;
    }

    /**
     * @method buildPaginatorElement
     */
    protected function buildPaginatorElement(): void
    {
        $this->paginator = new Paginator(
            $this->domTable->gettotalItems(),
            $this->domTable->getItemsPerPage(),
            $this->domTable->getCurrentPage(),
            $this->getUrlPattern()
        );

        if($this->paginator->getNumPages() > 1) {
            $this->paginatorContainer->appendChild(
                $this->paginator->getElement()
            );
            $this->mainContainer->appendChild($this->paginatorContainer);
        }
    }
}
