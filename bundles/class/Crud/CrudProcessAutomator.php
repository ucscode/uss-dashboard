<?php

use Ucscode\DOMTable\DOMTableInterface;
use Ucscode\SQuery\SQuery;
use Ucscode\UssElement\UssElement;

class CrudProcessAutomator implements CrudProcessAutomatorInterface
{
    private CrudIndexManager $crudIndexManager;
    private CrudEditManager $crudEditManager;
    private ?DOMTableInterface $crudIndexManagerUIParameter = null;
    private CrudEditSubmitInterface|CrudEditSubmitCustomInterface|null $crudEditManagerUIParameter = null;
    private null|CrudEditManager|CrudIndexManager $activeManager = null;

    private array $crudActions = [
        CrudActionImmutableInterface::ACTION_CREATE,
        CrudActionImmutableInterface::ACTION_UPDATE,
        CrudActionImmutableInterface::ACTION_DELETE,
        CrudActionImmutableInterface::ACTION_READ
    ];

    private ?string $currentAction;
    private ?string $currentEntity;

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
     * @method processOverviewAction
     */
    public function processOverviewAction(): void
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
            $this->crudEditManager->setReadOnly(true);
            $this->crudEditManager->setItemBy($key, $this->currentEntity);
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
        $this->crudIndexManager->manageBulkActionSubmission(
            new class ($this->crudIndexManager) implements CrudBulkActionsInterface {
                public function __construct(
                    private CrudIndexManager $crudIndexManager
                ) {
                }

                public function onSubmit(string $action, array $selections): void
                {
                    $primaryKey = $this->crudIndexManager->getPrimaryKey();
                    $uss = Uss::instance();

                    if($action === CrudActionImmutableInterface::ACTION_DELETE) {
                        foreach($selections as $value) {
                            $sQuery = (new SQuery())
                                ->delete($this->crudIndexManager->tablename)
                                ->where($primaryKey, $value);

                            $result = $uss->mysqli->query($sQuery);
                        }
                    }
                }
            }
        );
    }

    /**
     * @method processAllActions
     */
    public function processAllActions(): void
    {
        $this->processBulkActions();
        $this->processOverviewAction();
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
        if($this->activeManager instanceof CrudIndexManager) {
            $createdUI = $this->activeManager->createUI(
                $this->crudIndexManagerUIParameter
            );
        } elseif($this->activeManager instanceof CrudEditManager) {
            $createdUI = $this->activeManager->createUI(
                $this->crudEditManagerUIParameter
            );
        } else {
            $createdUI = null;
        };
        return $createdUI;
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

    /**
     * @method setCrudIndexUIParameter
     */
    public function setCrudIndexUIParameter(?DOMTableInterface $modifier): void
    {
        $this->crudIndexManagerUIParameter = $modifier;
    }

    /**
     * @method setCrudEditUIParameter
     */
    public function setCurdEditUIParameter(null|CrudEditSubmitInterface|CrudEditSubmitCustomInterface $modifier): void
    {
        $this->crudEditManagerUIParameter = $modifier;
    }
}
