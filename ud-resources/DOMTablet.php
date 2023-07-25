<?php

class DOMTablet extends DOMTableWidget
{
    protected $paginateNode;

    protected $marker = 'paged';

    private $wrapper;

    private $paginate = true;

    /**
     * Constructor
     *
     * @param string|null $tablename The name of the table
     */
    public function __construct(?string $tablename = null, ?string $class = null)
    {

        parent::__construct($tablename);

        /**
         * Create a new `DIV` container to display paginator
         */
        $this->paginateNode = $this->doc->createElement('div');
        $this->paginateNode->setAttribute('class', 'mt-3 dt-paginate');
        $this->container->appendChild($this->paginateNode);

        /**
         * Add custom `dataset` attribute to the table container
         */
        $this->container->setAttribute('data-domtablet', $this->tablename);

        /**
         * Quickly add custom class to container
         */
        $this->container->setAttribute('class', trim($this->container->getAttribute('class') . " {$class}"));

        /**
         * Get the current page being viewed
         */
        $this->page($_GET[ $this->marker ] ?? 1);

    }

    /**
     * Wrap table around a white card
     * Reverse this action by using `DOMTablet::unwrap()` method
     *
     * @param string class The class name of the wrapper
     */
    public function wrap(string $class = 'domtable-wrapper')
    {

        // If wrapped, ignore
        if($this->wrapper) {
            return;
        }

        /**
         * Create a wrapper element
         * And add the class name
         */
        $this->wrapper = $this->doc->createElement('div');
        $this->wrapper->setAttribute('class', $class);

        /** Create a bootstrap card */
        $card = $this->doc->createElement('div');
        $card->setAttribute('class', "card");

        /** Create a card-body */
        $card_body = $this->doc->createElement('div');
        $card_body->setAttribute('class', 'card-body');

        /**
         * Group the element:
         * - wrapper
         * - card
         * - card-body
         */
        $card->appendChild($card_body);
        $this->wrapper->appendChild($card);

        /**
         * Position the element
         * And then, append the table into it
         */
        $this->container->insertBefore($this->wrapper, $this->table->parentNode);
        $card_body->appendChild($this->table->parentNode);

    }

    /**
     * Remove the wrapper around the table element
     */
    public function unwrap()
    {

        // if not wrapped, ignore
        if(!$this->wrapper) {
            return;
        }

        /** Restore the table */
        $this->container->insertBefore($this->table->parentNode, $this->wrapper);

        /** Delete the wrapper */
        $this->wrapper->parentNode->removeChild($this->wrapper);
        $this->wrapper = null;

    }

    /**
     *
     */
    public function paginate(bool $value) {
        $this->paginate = $value;
    }

    /**
     * Determin whether checkbox should be applied or not
     *
     * @return bool
     *
     * @ignore
     */
    private function checked(?array $data = null)
    {
        /**
         * Get key and confirm check status
         */
        $key = "bulk{$this->widgetSuffix}";
        $checked = !empty($this->widgets[ $key ]) && !empty($this->columns[0]) && !empty($this->primaryKey);

        ## ???
        if(is_array($data)) {
            $approved = array_key_exists($this->primaryKey, $data);
            $checked = $checked && $approved;
        }

        /**
         * Return check status
         */
        return $checked;

    }

    /**
     * Override parent method `modify_data`
     * Then modify the data so that checkboxes can be added to the table
     *
     * @return array containing the modified data
     * @ignore
     */
    protected function modify_data($data, $func)
    {
        /**
         * Get the parent modified data
         * Then add extra modification
         */
        $new_data = parent::modify_data($data, $func);

        /**
         * Confirm if checkbox should be added
         * Then add checkbox if `true` is returned
         */
        if($this->checked($data)) {
            /**
             * Get the value of primary key
             */
            $primary_value = htmlspecialchars($new_data[ $this->primaryKey ], ENT_QUOTES | ENT_HTML5);

            /**
             * Create the checkbox and add the value;
             * checkbox prefix starts with `:` because SQL column cannot start with that value
             * Hence, any modification to the checkbox is considered delibrate
             */
            $new_data[':checked'] = "
				<div class='form-check'>
					<input type='checkbox' name='ud-bulk[values][]' form='dt-{$this->tablename}-bulk' data-check='{$this->tablename}' class='form-check-input' value='{$primary_value}' />
				</div>
			";
        };

        /**
         * Return the new value
         * With or without checkbox included
         */
        return $new_data;
    }

    /**
     * Override Parent Prepare Method
     *
     * @param callable $func A callback to modify the table output
     * @param bool print If set to true, it prints the table directly on browser. Else, it returns the table string
     *
     * @return string|int
     */
    public function prepare(?callable $func = null, bool $print = false)
    {

        /**
         * If checkbox is added to the data
         * Then add a checkbox to the `<thead/>` which can be used to select multiple checkboxes
         */
        if($this->checked()) {
            $this->columns[0] = array(
                ':checked' => "<div class='form-check'>
					<input type='checkbox' class='form-check-input' data-check-all='{$this->tablename}'/>
				</div>"
            ) + $this->columns[0];
        };

        /**
         * Prepare the table but do not print it
         */
        parent::prepare($func, false);

        /**
         * Get all widgets and append it to the table
         */
        foreach([$this->widgets, $this->customWidgets] as $widget) {
            /**
             * Loop through the widget indexes
             */
            foreach($widget as $div) {
                /**
                 * If the widget contains element
                 * Append it to the widget block
                 */
                if($div) {
                    $this->widgetBlock->appendChild($div);
                }
            }
        };

        /**
         * Create pagination to move between next and previous page
         */
        if( $this->paginate ) {
            $this->createNav($this->pagination(1));
        };

        /**
         * Get the HTML String of the table
         */
        $table = $this->doc->saveHTML($this->container);

        /**
         * Print on demand
         */
        return print_r($table, !$print);

    }

    /**
     * Create a range of pages
     *
     * @ignore
     */
    protected function pagination(int $siders)
    {
        /**
         * If no extra page is available, return
         */
        if(!$this->pages) {
            return [];
        }

        /**
         * Siders means How many indexes should be by the sides of the current page
         *
         * Example:
         * > [1] (2) [3]; if current page is 2, $siders = 1
         * Example 2:
         * > [1 2] (3) [4 5]; If current page is 3, $siders = 2;
         *
         * $midpoint specifies the position of the current page
         */
        $midpoint = $siders + 1;
        $limit = $midpoint + $siders;

        /**
         * Define Page Ranges
         */
        if($this->page < $midpoint) {
            $last = ($this->pages > $limit) ? $limit : $this->pages;
            $range = range(1, $last);
        } elseif($this->page > ($this->pages - $siders)) {
            $first = ($this->pages - $limit + 1);
            if($first < 1) {
                $first = 1;
            }
            $range = range($first, $this->pages);
        } else {
            $range = range($this->page - $siders, $this->page + $siders);
        };

        /**
         * Return an array containing ranges
         */
        return array_combine($range, $range);

    }

    /**
     * Create a pagination DIV Element
     * This will be clicked to navigate from one page to another
     */
    protected function createNav(array $range)
    {

        if(count($range) < 2) {
            return;
        }

        $list = [];

        if(!in_array(1, array_keys($range))) {
            $range = array( 1 => "<i class='bi bi-arrow-left-short'></i>" ) + $range;
        };

        if(!in_array($this->pages, array_keys($range))) {
            $range[ $this->pages ] = "<i class='bi bi-arrow-right-short'></i>";
        }

        foreach($range as $index => $text) {
            $active = ($index == $this->page) ? 'active' : null;
            $list[] = "
				<li class='page-item {$active}'>
					<button class='page-link' name='{$this->marker}' type='submit' value='{$index}'>{$text}</button>
				</li>
			";
        };

        $buttons = implode('', $list);

        $data = $_GET;
        unset($data['query']);

        if(isset($data[ $this->marker ])) {
            unset($data[ $this->marker ]);
        }

        $queries = '';
        foreach($data as $key => $value) {
            $key = urlencode($key);
            $value = urlencode($value);
            $queries .= "<input type='hidden' name='{$key}' value='{$value}' />";
        }

        $this->innerHTML($this->paginateNode, "
			<form method='GET'>
				{$queries}
				<ul class='pagination'>
					{$buttons}
				</ul>
			</form>
		");

    }


}
