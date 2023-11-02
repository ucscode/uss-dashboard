<?php

use Ucscode\UssElement\UssElement;
use Ucscode\UssForm\UssForm;
use Ucscode\DOMTable\DOMTable;
use Ucscode\SQuery\SQuery;

abstract class AbstractCrudIndexConcept extends AbstractCrudIndexManager
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
     * @method configureProperties
     */
    protected function configureProperties(): void
    {
        $tableColumns = array_map(function ($value) {
            return str_replace('_', ' ', $value);
        }, Uss::instance()->getTableColumns($this->tablename));

        $this->setTableColumns($tableColumns);

        $basicNodes = [
            'mainBlock' => 'crud-container',
            'widgetBlock' => 'crud-widget row',
            'paginatorBlock' => 'crud-paginator',
            'tableBlock' => 'crud-table border-top border-bottom my-2 py-2'
        ];

        foreach($basicNodes as $key => $classname) {
            $this->{$key} = new UssElement(UssElement::NODE_DIV);
            $this->{$key}->setAttribute('class', $classname);
        };

        $this->tableForm = new UssForm(
            $this->tablename . '-crud-form',
            $_SERVER['REQUEST_URI'],
            'POST'
        );

        $this->tableForm->setAttribute('data-ui-crud-form', self::CRUD_NAME);

        $this->domTable = new DOMTable($this->tablename);

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
        /**
         * NO ACTION
         */
        $noAction = (new CrudAction())
            ->setLabel('- Select Action -')
            ->setElementAttribute('value', '');

        $this->addBulkAction('no-action', $noAction);

        /**
         * DELETE ACTION
         */
        $deleteAction = (new CrudAction())
            ->setLabel('Delete')
            ->setElementAttribute('value', 'delete')
            ->setElementAttribute('data-ui-confirm', '{{items}} items will be deleted! <br> Are you sure you want to proceed?');

        $this->addBulkAction(self::ACTION_DELETE, $deleteAction);
    }

    /**
     * @method configureItemActions
     */
    protected function setDefaultItemActions(): void
    {
        $this->addItemAction(self::ACTION_READ, new CrudItemReadAction($this));
        $this->addItemAction(self::ACTION_UPDATE, new CrudItemUpdateAction($this));
        $this->addItemAction(self::ACTION_DELETE, new CrudItemDeleteAction($this));
    }

    /**
     * @method setDefaultWidgets
     */
    protected function setDefaultWidgets(): void
    {
        $searchContainer = $this->createSearchWidget();
        $this->setWidget('search', $searchContainer);

        $newContainer = $this->createAddNewWidget();
        $this->setWidget('add-new', $newContainer);
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
     * @method createAddNewWidget
     */
    protected function createAddNewWidget(): UssElement
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $query = ['action' => self::ACTION_CREATE];
        $href = $path . "?" . http_build_query($query);

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
}
