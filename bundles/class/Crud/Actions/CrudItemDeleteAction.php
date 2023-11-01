<?php

class CrudItemDeleteAction implements CrudActionInterface
{
    public function __construct(
        private CrudIndexManager $crudIndexManager
    ) {
    }

    public function foreachItem(array $item): CrudAction
    {
        $modalMessage = "This action cannot be reversed! <br> Are you sure you want to proceed?";

        $crudAction = (new CrudAction())
            ->setLabel('Delete')
            ->setIcon('bi bi-trash')
            ->setElementType(CrudAction::TYPE_ANCHOR)
            ->setElementAttribute('href', $this->getHref($item))
            ->setElementAttribute('data-ui-confirm', $modalMessage)
            ->setElementAttribute('data-ui-size', 'small');

        if($this->crudIndexManager->isDisplayItemActionsAsButton()) {
            $crudAction->setElementAttribute('class', 'btn btn-outline-danger btn-sm text-nowrap');
        }

        return $crudAction;
    }

    protected function getHref(array $item): string
    {
        $key = $this->crudIndexManager->getPrimaryKey();
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $query = [
            'action' => CrudActionImmutableInterface::ACTION_DELETE,
            'entity' => $item[$key] ?? ''
        ];
        $href = $path . "?" . http_build_query($query);
        return $href;
    }
}
