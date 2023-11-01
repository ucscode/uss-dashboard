<?php

class CrudItemUpdateAction implements CrudActionInterface
{
    public function __construct(
        private CrudIndexManager $crudIndexManager
    ) {
    }

    public function forEachItem(array $item): CrudAction
    {
        $curdAction = (new CrudAction())
            ->setLabel('Edit')
            ->setIcon('bi bi-pen')
            ->setElementType(CrudAction::TYPE_ANCHOR)
            ->setElementAttribute('href', $this->getHref($item));

        return $curdAction;
    }

    protected function getHref(array $item): string
    {
        $key = $this->crudIndexManager->getPrimaryKey();
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $query = [
            'action' => CrudActionImmutableInterface::ACTION_UPDATE,
            'entity' => $item[$key] ?? ''
        ];
        $href = $path . "?" . http_build_query($query);
        return $href;
    }
}
