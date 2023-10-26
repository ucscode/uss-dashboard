<?php

use Ucscode\DOMTable\DOMTableInterface;
use Ucscode\DOMTable\DOMTable;
use Ucscode\SQuery\SQuery;

abstract class AbstractCrudIndexManager
{
    public const INDEX_KEY = 'page';
    protected $uss;
    protected SQuery $sQuery;
    public readonly Paginator $paginator;
    protected array $columns;
    protected int $startingIndex = 0;

    public function __construct(
        public readonly string $tablename
    ) {
        $this->uss = Uss::instance();
        $this->columns = $this->uss->getTableColumns($this->tablename);
        $this->paginator = new Paginator(0, 10, 1, $this->getRequestPattern());
        $this->buildIndexQuery();
    }

    /**
     * @method __debugInfo
     */
    public function __debugInfo(): array
    {
        $vars = array_keys(get_class_vars(get_called_class()));
        $output = [];
        foreach($vars as $var) {
            if(isset($this->{$var})) {
                if(in_array($var, ['sQuery', 'uss'])) {
                    $result = 'object(' . $this->{$var}::class . ')';
                } else {
                    $result = $this->{$var};
                }
                $output[$var] = $result;
            }
        }
        return $output;
    }

        /**
     * @method buildQuery
     */
    protected function buildIndexQuery(): void
    {
        $this->sQuery = (new SQuery())
            ->select()
            ->from($this->tablename);
            
        $this->inspectQuery();
    }

    /**
     * @method countQuery
     */
    protected function inspectQuery(): void
    {
        $clone = clone $this->sQuery;
        $clone->select('COUNT(*) AS items');
        $result = $this->uss->mysqli->query($clone->getQuery());

        $this->paginator->setTotalItems(
            $result->fetch_assoc()['items']
        );

        $this->paginator->setCurrentPage(
            $this->getCurrentIndexPage()
        );

        $this->startingIndex = 
            ($this->paginator->getCurrentPage() - 1) * $this->paginator->getItemsPerPage();

    }

    /**
     * @method getCurrentPage
     */
    protected function getCurrentIndexPage(): int 
    {
        $page = $_GET['page'] ?? null;
        $page = !is_numeric($page) ? 1 : abs($page);
        $totalItems = $this->paginator->getTotalItems();
        $itemsPerPage = $this->paginator->getItemsPerPage();
        $maxPages = $totalItems ? ceil($totalItems / $itemsPerPage) : 1;
        if($page > $maxPages) {
            $page = $maxPages;
        };
        return $page;
    }

    /**
     * @method getRequestPattern
     */
    protected function getRequestPattern(): string
    {
        $url = parse_url($_SERVER['REQUEST_URI']);
        $path = $url['path'];
        $query = '';
        if(array_key_exists('page', $_GET)) {
            unset($_GET['page']);
        };
        $query = http_build_query($_GET);
        if(!empty($query)) {
            $query .= '&';
        };
        $query .= self::INDEX_KEY . '=(:num)';
        $path .= '?' . $query;
        return $path;
    }
}