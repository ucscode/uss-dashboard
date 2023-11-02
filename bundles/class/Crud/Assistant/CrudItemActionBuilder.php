<?php

final class CrudItemActionBuilder implements CrudActionImmutableInterface
{
    public function __construct(
        protected CrudEditManager $crudEditManager
    ){
        $this->buildCreateAction();
        $this->buildUpdateAction();
        $this->buildDeleteAction();
        $this->buildIndexAction();
    }

    /**
     * @method href
     */
    protected function href(array $query = []): string
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if(!empty($query)) {
            $query = $query + $_GET;
            $path .= "?" . http_build_query($query);
        };
        return $path;
    }

    /**
     * @method buildCreateAction
     */
    protected function buildCreateAction(): void
    {
        $this->crudEditManager->setAction(
            self::ACTION_CREATE,
            $this->newCrudAction()
                ->setElementAttribute('value', self::ACTION_CREATE)
                ->setLabel('Add New')
                ->setIcon('bi bi-plus-circle')
        );
    }

    /**
     * @method buildUpdateAction
     */
    protected function buildUpdateAction(): void
    {
        if($this->crudEditManager->getCurrentAction() === self::ACTION_UPDATE) {
            $updateAction = $this->newCrudAction('btn-success')
                ->setElementAttribute('value', self::ACTION_UPDATE)
                ->setLabel('Save Changes')
                ->setIcon('bi bi-floppy');
        } else {
            $updateAction = $this->newCrudAction('btn-primary')
                ->setElementType(CrudAction::TYPE_ANCHOR)
                ->setElementAttribute('value', self::ACTION_UPDATE)
                ->setLabel('Edit Item')
                ->setIcon('bi bi-pen')
                ->setElementAttribute('href', $this->href([
                    'action' => self::ACTION_UPDATE,
                    'entity' => $this->crudEditManager->getCurrentEntity()
                ]));
        };

        $this->crudEditManager->setAction(
            self::ACTION_UPDATE,
            $updateAction
        );
    }

    /**
     * @method buildDeleteAction
     */
    protected function buildDeleteAction(): void
    {
        $this->crudEditManager->setAction(
            self::ACTION_DELETE,
            $this->newCrudAction('btn-danger')
                ->setElementAttribute('value', self::ACTION_DELETE)
                ->setLabel('Delete Item')
                ->setIcon('bi bi-trash')
                ->setElementType(CrudAction::TYPE_ANCHOR)
                ->setElementAttribute('href', $this->href([
                    'action' => self::ACTION_DELETE,
                    'entity' => $this->crudEditManager->getCurrentEntity()
                ]))
                ->setElementAttribute('data-ui-confirm', 'Are you sure you want to delete this item?')
        );
    }

    /**
     * @method buildIndexAction
     */
    protected function buildIndexAction(): void
    {
        $this->crudEditManager->setAction(
            self::ACTION_INDEX,
            $this->newCrudAction('btn-outline-info')
                ->setElementType(CrudAction::TYPE_ANCHOR)
                ->setLabel('Back To Listing')
                ->setElementAttribute('value', self::ACTION_INDEX)
                ->setElementAttribute('href', $_SERVER['HTTP_REFERER'] ?? $this->href())
        );
    }

    /**
     * @method newCrudAction
     */
    protected function newCrudAction(string $class = 'btn-primary'): CrudAction
    {
        return (new CrudAction())
            ->setElementAttribute('class', 'btn btn-sm m-1 ' . $class)
            ->setElementAttribute('name', '__ACTION__')
            ->setElementAttribute('type', 'submit');
    }
}