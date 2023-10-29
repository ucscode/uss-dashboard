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

    /**
     * @method createUI
     */
    public function createUI(?DOMTableInterface $fabricator = null): UssElement
    {
        $this->combineWidgetElements();
        
        $this->tableBlock->appendChild(
            $this->buildTableElements()
        );

        $this->mainBlock->appendChild($this->tableBlock);

        $this->buildDOMTable($fabricator);

        return $this->mainBlock;
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
            'mainBlock' => 'crud-container',
            'widgetBlock' => 'crud-widget row',
            'paginatorBlock' => 'crud-paginator',
            'tableBlock' => 'crud-table border-top border-bottom my-2 py-2'
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

        $this->sQuery = (new SQuery())
            ->select('*', $this->tablename)
            ->orderBy($this->getPrimarykey() . ' DESC');
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
        /**
         * UPDATE ACTION
         */
        $this->addItemAction(self::ACTION_UPDATE, new class ($this) implements CrudActionInterface 
        {
            public function __construct(
                private CrudIndexManager $crudIndexManager
            ){}

            public function forEachItem(array $item): CrudAction
            {
                $key = $this->crudIndexManager->getPrimaryKey();
                $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                $query = [
                    'action' => CrudActionImmutableInterface::ACTION_UPDATE,
                    'entity' => $item[$key] ?? ''
                ];

                $href = $path . "?" . http_build_query($query);

                $curdAction = (new CrudAction())
                    ->setLabel('Edit')
                    ->setIcon('bi bi-pen')
                    ->setElementType(CrudAction::TYPE_ANCHOR)
                    ->setElementAttribute('href', $href);

                return $curdAction;
            }
        });

        /**
         * DELETE ACTION
         */
        $this->addItemAction(self::ACTION_DELETE, new class ($this) implements CrudActionInterface {
            public function __construct(
                private CrudIndexManager $crudIndexManager
            ) {
            }

            public function forEachItem(array $item): CrudAction
            {
                $key = $this->crudIndexManager->getPrimaryKey();
                $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                $query = [
                    'action' => CrudActionImmutableInterface::ACTION_DELETE,
                    'entity' => $item[$key] ?? ''
                ];

                $href = $path . "?" . http_build_query($query);

                $modalMessage = "<span class='fs-14px'>
                    Are you sure you want to delete the item? <br> 
                    This action cannot be reversed</span>
                ";

                $crudAction = (new CrudAction())
                    ->setLabel('Delete')
                    ->setIcon('bi bi-trash')
                    ->setElementType(CrudAction::TYPE_ANCHOR)
                    ->setElementAttribute('href', $href)
                    ->setElementAttribute('data-ui-confirm', $modalMessage)
                    ->setElementAttribute('data-ui-size', 'small');

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
        $tableElement = $this->tableForm;
        $tableWrapper = $this->domTable->getTableWrapperElement();
    
        if($this->isTableWhiteBackground()) {
            $tableWrapper->addAttributeValue('class', 'p-3 bg-white');
        }

        if(!$this->hideBulkActions) {
            $bulkActionContainer = $this->buildBulkActionsElement();
            $this->tableForm->appendChild($bulkActionContainer);
            $this->tableForm->appendChild($tableWrapper);
        }
        
        return $tableElement;
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
                $this->widgetBlock->appendChild($widget);
            }
            $this->mainBlock->appendChild($this->widgetBlock);
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
            $this->paginatorBlock->appendChild(
                $this->paginator->getElement()
            );
            $this->mainBlock->appendChild($this->paginatorBlock);
        }
    }
}
