<?php

use Ucscode\SQuery\SQuery;

class CrudBulkActionHandler implements CrudBulkActionsInterface
{
    protected CrudIndexManager $crudIndexManager;
    protected string $primaryKey;
    protected string $action;
    protected array $selections;

    /**
     * @method __constructor
     */
    public function __construct(CrudIndexManager $crudIndexManager)
    {
        $this->crudIndexManager = $crudIndexManager;
        $this->primaryKey = $this->crudIndexManager->getPrimaryKey();
    }

    /**
     * @overrides onSubmit
     */
    public function onSubmit(string $action, array $selections): void
    {
        $this->action = $action;
        $this->selections = $selections;
        $this->handleDelete();
    }

    /**
     * @method handleDelete
     */
    protected function handleDelete(): void
    {
        if($this->action === CrudActionImmutableInterface::ACTION_DELETE) {
            $sQuery = (new SQuery())
                ->delete($this->crudIndexManager->tablename)
                ->where($this->primaryKey, $this->selections);

            Uss::instance()->mysqli->query($sQuery);

            $this->crudIndexManager->updateSQuery(function ($sQuery) {
                return $sQuery;
            });

        }
    }
}
