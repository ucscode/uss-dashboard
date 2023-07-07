<?php


defined('UDASH_DIR') or die;

abstract class DOMTableWidget extends DOMTable
{
    protected $widgets = array(); // System Widget Elements;

    protected $customWidgets = array(); // Custom Widget Elements;

    protected $widgetBlock; // Widget Node

    protected $widgetSuffix = "WidgetApp";

    protected $primaryKey = null;
    protected $bulkData;

    /**
     * DOMTableWidget Constructor
     */
    public function __construct(?string $tablename)
    {

        // Supress Error Message
        libxml_use_internal_errors(true);

        // Call DOMTable::__construct();
        parent::__construct($tablename);

        /**
         * Create a `DIV` container to append table Widgets
         */
        $this->widgetBlock = $this->doc->createElement('div');
        $this->widgetBlock->setAttribute('class', 'row row-cols-auto dt-widgets');
        $this->container->insertBefore($this->widgetBlock, $this->container->firstChild);

    }

    /**
     * Access & configure a widget
     *
     * @param string $widgetName The name of the widget
     * @param mixed $value
     * @param mixed $extraAug Extra argument to pass to widget if required
     */
    public function setWidget(string $widgetName, $value = null, $extraAug = null)
    {
        /**
         * Add widget suffix
         */
        $widgetName .= $this->widgetSuffix;

        if(!method_exists($this, $widgetName)) {
            /**
             * If widget method does not exists
             * Throw an exception
             */
            throw new \Exception("Unknown DOMTablet widget - {$widgetName}");
        };

        /**
         * All Built In Widget Must be private
         * Check if the widget is private
         */
        $reflex = (new ReflectionMethod($this, $widgetName));
        if(!$reflex->isPrivate()) {
            /**
             * Throw exception if it is not
             */
            throw new \Exception("Trying to access method {$reflex->class}::{$widgetName}() as widget");
        };

        return call_user_func([$this, $widgetName], $value, $extraAug);

    }

    /**
     * Establish Built In Search Widget
     * @ignore
     */
    private function searchWidgetApp(bool $apply)
    {

        $key = __FUNCTION__;

        if($apply):

            /**
             * Create search `DIV` container
             */
            $div = $this->doc->createElement('div');
            $div->setAttribute('class', 'col dt-uss-search');

            /**
             * Capture the search keyword
             */
            $searchWord = $_GET['search'] ?? null;

            /**
             * Add Inner HTML into the DIV
             * `innerHTML` is an inherited method
             */
            return $this->widgets[ $key ] = $this->innerHTML($div, "
				<form method='GET'>
					<div class='d-flex mb-2'>
						<div class='w-100 me-1'>
							<input type='text' name='search' class='form-control text-sm' placeholder='search' value='{$searchWord}'>
						</div>
						<div class='col-'>
							<button class='btn btn-outline-info text-sm'>
								<i class='bi bi-search'></i>
							</button>
						</div>
					</div>
				</form>
			");

        endif;

        /**
         * Remove Widget
         */
        if(isset($this->widgets[$key])) {
            unset($this->widgets[$key]);
        }

    }

    /**
     * Allow bulk selection of rows
     * The bulk selection form is submitted as a `$_POST` request
     * The $_POST request will contain an array with the key `ud-bulk` and this array will contain the following keys
     *
     * 1. table: The name of the DOMTable
     * 2. nonce: A nonce tested against the table name
     * 3. action: The option that was selected
     * 4. values: An array containing index of selected rows
     *
     * @ignore
     */
    private function bulkWidgetApp(?array $data, ?string $primaryKey = null)
    {

        $key = __FUNCTION__;

        if(!empty($data)):

            /**
             * Create the bulk `DIV` Container
             */
            $div = $this->doc->createElement('div');
            $div->setAttribute('class', 'col col-md-3 dt-uss-bulk');

            /** Generate Nonce */
            $nonce = Uss::nonce($this->tablename);

            /**
             * Add element to the bulk container;
             */
            $this->widgets[ $key ] = $this->innerHTML($div, "
				<form method='POST' id='dt-{$this->tablename}-bulk'>
					<input type='hidden' name='ud-bulk[table]' value='{$this->tablename}'>
					<input type='hidden' name='ud-bulk[nonce]' value='{$nonce}'>
					<div class='d-flex mb-2'>
						<div class='w-100 me-1'>
							<select name='ud-bulk[action]' class='form-control text-sm text-capitalize' required=''>
								<option value=''>Select One</option>
							</select>
						</div>
						<button class='btn btn-primary text-sm'>Apply</button>
					</div>
				</form>
			");

            /**
             * Append the more option into the `<select/>` element
             * The option are created from the bulk data specifications
             */
            $select = $div->getElementsByTagName('select')->item(0);

            /**
             * Loop data & add options
             */
            foreach($data as $index => $value) {
                $option = $this->doc->createElement('option');
                $option->setAttribute('value', htmlspecialchars($index, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $text = $this->doc->createTextNode($value);
                $option->appendChild($text);
                $select->appendChild($option);
            };

            /**
             * Save the primary key
             * Save the bulk data
             */
            $this->primaryKey = $primaryKey;
            $this->bulkData = $data;

            return $this->widgets[ $key ];

        endif;

        /**
         * Remove Bulk Column
         */
        if(isset($this->widgets[$key])) {
            unset($this->widgets[$key]);
        }

    }

    /**
     * Add custom widget and execute a function
     *
     * @param string $name The name of the custom widget
     * @param string $innerHTML The innerHTML of the element
     * @param callable|null $func A callback to run after the innerHTML DOMElement has been successfully created
     *
     * @return bool
     */
    public function setCustomWidget(string $name, ?string $innerHTML, ?callable $func = null)
    {

        /**
         * If parameter is empty, widget will be removed
         */
        if(array_key_exists($name, $this->customWidgets) && empty($innerHTML)) {
            unset($this->customWidgets[ $name ]);
            return;
        };

        /**
         * Create custom widget!
         */
        $div = $this->doc->createElement('div');
        $div->setAttribute('class', "col dt-custom-{$name}");

        /** Add innerHTML */
        $this->innerHTML($div, $innerHTML);
        $this->customWidgets[ $name ] = $div;

        /**
         * Run Custom Function
         * To modify the created widget
         */
        if($func) {
            $func($div);
        }

        /** Will always return `true` */
        return isset($this->customWidgets[ $name ]);

    }


}
