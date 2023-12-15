<?php

use Ucscode\UssElement\UssElement;

class CrudProcessAutomator implements CrudProcessAutomatorInterface
{
    private CrudIndexManager $crudIndexManager;
    private CrudEditManager $crudEditManager;
    private null|CrudEditManager|CrudIndexManager $activeManager = null;

    private array $crudActions = [
        CrudActionImmutableInterface::ACTION_CREATE,
        CrudActionImmutableInterface::ACTION_UPDATE,
        CrudActionImmutableInterface::ACTION_DELETE,
        CrudActionImmutableInterface::ACTION_READ
    ];

    private ?string $currentAction;
    private ?string $currentEntity;

    /**
     * Perfect!
     */
    public function __construct(
        protected string $tablename
    ) {
        $this->currentAction = htmlentities($_GET['action'] ?? '');
        $this->currentEntity = htmlentities($_GET['entity'] ?? '');
        $this->crudIndexManager = new CrudIndexManager($this->tablename);
        $this->crudEditManager = new CrudEditManager($this->tablename);
    }

    /**
     * @method getCurrentAction
     */
    public function getCurrentAction(): string
    {
        return $this->currentAction;
    }

    /**
     * @method processIndexAction
     */
    public function processIndexAction(): void
    {
        if(!in_array($this->currentAction, $this->crudActions, true)) {
            $this->activeManager = $this->crudIndexManager;
        }
    }

    /**
     * @method processCreateAction
     */
    public function processCreateAction(): void
    {
        if($this->currentAction === CrudActionImmutableInterface::ACTION_CREATE) {
            $this->activeManager = $this->crudEditManager;
        }
    }

    /**
     * @method processReadAction
     */
    public function processReadAction(): void
    {
        if($this->currentAction === CrudActionImmutableInterface::ACTION_READ) {
            $key = $this->crudEditManager->getPrimaryKey();
            $this->crudEditManager->setItemBy($key, $this->currentEntity);
            $this->crudEditManager->setReadOnly(true);
            $this->activeManager = $this->crudEditManager;
        }
    }

    /**
     * @method processUpdateAction
     */
    public function processUpdateAction(): void
    {
        if($this->currentAction === CrudActionImmutableInterface::ACTION_UPDATE) {
            $key = $this->crudEditManager->getPrimaryKey();
            $this->crudEditManager->setItemBy($key, $_GET['entity'] ?? null);
            $this->activeManager = $this->crudEditManager;
        }
    }

    /**
     * @method processDeleteAction
     */
    public function processDeleteAction(): void
    {
        if($this->currentAction === CrudActionImmutableInterface::ACTION_DELETE) {
            $key = $this->crudEditManager->getPrimaryKey();
            $this->crudEditManager->setItemBy($key, $_GET['entity'] ?? null);
            $deleted = $this->crudEditManager->deleteItemEntity();
            if($deleted) {
                $this->crudEditManager->rewriteCurrentPath();
            }
        }
    }

    /**
     * @method processBulkActions
     */
    public function processBulkActions(): void
    {
        $this->crudIndexManager->handleBulkActions(
            new CrudBulkActionHandler($this->crudIndexManager)
        );
    }

    /**
     * @method processAllActions
     */
    public function processAllActions(): void
    {
        $this->processBulkActions();
        $this->processIndexAction();
        $this->processCreateAction();
        $this->processReadAction();
        $this->processUpdateAction();
        $this->processDeleteAction();
    }

    /**
     * @method getCreatedUI
     */
    public function getCreatedUI(): ?UssElement
    {
        return $this->activeManager?->createUI();
    }

    /**
     * @method getCrudIndexManager
     */
    public function getCrudIndexManager(): CrudIndexManager
    {
        return $this->crudIndexManager;
    }

    /**
     * @method getCrudEditManager
     */
    public function getCrudEditManager(): CrudEditManager
    {
        return $this->crudEditManager;
    }
}
