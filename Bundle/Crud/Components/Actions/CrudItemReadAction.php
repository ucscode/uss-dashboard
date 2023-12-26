<?php

class CrudItemReadAction implements CrudActionInterface
{
    public function __construct(
        protected CrudIndexManager $crudIndexManager
    ) {
    }

    public function foreachItem(array $item): CrudAction
    {
        $crudAction = (new CrudAction())
            ->setLabel('View')
            ->setIcon('bi bi-eye')
            ->setElementType(CrudAction::TYPE_ANCHOR)
            ->setElementAttribute('href', $this->getHref($item));

        return $crudAction;
    }

    protected function getHref(array $item): string
    {
        $key = $this->crudIndexManager->getPrimaryKey();
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $query = [
            'action' => CrudActionImmutableInterface::ACTION_READ,
            'entity' => $item[$key] ?? ''
        ];
        $href = $path . "?" . http_build_query($query);
        return $href;
    }
}
