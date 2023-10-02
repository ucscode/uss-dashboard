<?php

use Ucscode\UssForm\UssForm;

abstract class AbstractUdashForm extends UssForm implements UdashFormInterface
{
    abstract protected function buildForm();

    /**
     * Default styles and settings for form elements.
     *
     * @var array
     */
    protected array $style = [
        'label_class' => 'd-none',
        'required' => true,
        'column' => 'col-12 mb-2',
    ];

    /**
     * Whether the form is secured with nonces.
     *
     * @var bool
     */
    protected bool $secured = false;

    /**
     * Whether the form is submitted.
     *
     * @var bool
     */
    private bool $submitted = false;

    /**
     * Whether the form submission is trusted based on nonce
     *
     * @var bool
     */
    private bool $trusted = false;

    /**
     * A key to validate nonce
     *
     * @var bool
     */
    private string $nonceKey;

    /**
     * A set of reusable data
     *
     * @var array
     */
    private array $links = [];

    /**
     * Constructor for the UdashForm class.
     *
     * @param string $name The name of the form.
     * @param string|null $action The URL where the form data will be submitted (default: null, which uses the current request URI).
     * @param string $method The HTTP method for form submission (default: 'POST').
     * @param string $enctype The enctype attribute for the form (default: empty string).
     *
     * @return void
     */
    public function __construct(string $name, ?string $action = null, string $method = 'POST', string $enctype = '')
    {
        if(empty($action)) {
            $action = $_SERVER['REQUEST_URI'];
        }
        parent::__construct($name, $action, $method, $enctype);
        $this->initForm();
        $this->beforeBuild();
        $this->buildForm();
    }

    /**
     * An alternative way to mimic __construct for child class
     * Since the constructor does many process, trying to redefine all parameters on child class and call parent::__construct()
     * method can be time wasting. Override the constructor instead
     */
    protected function beforeBuild()
    {
        // Does nothing, just for overriding
    }

    /**
     * This creates a dedicated process for handling form submission
     * @return void
     */
    public function handleSubmission(): void
    {
        if($this->isSubmitted()) {

            if($this->isTrusted()) {

                // Get Filtered data from _GET or _POST
                $post = $this->getFilteredSubmissionData();

                // Validate The Data
                if($this->isValid($post)) {

                    // Get data that should be inserted to database;
                    $post = $this->prepareEntryData($post);

                    // Update Or Insert Data To Database
                    $persist = $this->persistEntry($post);

                    if($persist) {
                        $this->onEntrySuccess($post);
                    } else {
                        $this->onEntryFailure($post);
                    }

                } else {
                    $this->handleInvalidRequest($post);
                };

            } else {
                $this->handleUntrustedRequest();
            }

        };

    }

    /**
     * This method should save information to database and must return true or false regarding whether the data was saved or not
     *
     * @param array $data The data to persist to database
     *
     * @throws Exception if not overridden
     *
     * @return bool `true` if the data saved, `false` otherwise
     */
    public function persistEntry(array $data): bool
    {
        $this->formulateError(__METHOD__, 'to save entry into database');
    }

    /**
     * This method is called if the data failed to save in database
     *
     * @param array $data The data that did not save to database
     *
     * @throws Exception if not overridden
     *
     * @return void
     */
    public function onEntryFailure(array $data): void
    {
        $this->formulateError(__METHOD__, 'to manage actions upon failure on database entry.');
    }

    /**
     * This method is called if the data successfully save to database
     *
     * @param array $data The data that saved to database
     *
     * @throws Exception if not overridden
     *
     * @return void
     */
    public function onEntrySuccess(array $data): void
    {
        $this->formulateError(__METHOD__, 'to manage actions upon successful database entry.');
    }

    /**
     * Get the data to be persisted to the database.
     *
     * @param array $data The original data to be processed and persisted.
     *
     * @return array The prepared data ready for database storage.
     */
    public function prepareEntryData(array $data): array
    {
        return $data;
    }

    /**
    * Handle an untrusted request.
    *
    * This method is called when a request is detected from an invalid source or with an attack.
    * You can implement custom logic here to handle such requests, such as logging, blocking, or other actions.
    *
    * @return void
    */
    public function handleUntrustedRequest(): void
    {
        // Should be overridden by child class
    }

    /**
    * Handle an invalid request.
    *
    * This method is called when a request is considered invalid, such as when it contains
    * invalid data (e.g., an invalid email address).
    *
    * @param array|null $post The post data associated with the invalid request.
    *
    * @return void
    */
    public function handleInvalidRequest(?array $post): void
    {
        // Should be overridden by child class
        $this->populate($post);
    }

    /**
    * Check if the provided post data is valid.
    *
    * @param array|null $post The post data to validate.
    *
    * @return bool Returns true if the post data is empty or null; otherwise, false.
    */
    public function isValid(?array $post): bool
    {
        if($post === null) {
            $post = $this->getFilteredSubmissionData();
        };
        return !empty($post);
    }

    /**
    * Get filtered submission data.
    *
    * @param bool $sanitize Whether to sanitize the data (default: true).
    *
    * @return array The filtered submission data.
    */
    public function getFilteredSubmissionData(bool $sanitize = true): array
    {
        $data = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
        if(isset($data['udf-hash'])) {
            unset($data['udf-hash']);
        };
        array_walk_recursive($data, function (&$value) use ($sanitize) {
            $value = trim($value);
            if($sanitize) {
                $value = htmlspecialchars(trim($value), ENT_QUOTES);
            };
        });
        return $data;
    }

    /**
     * Check if the form has been submitted and return POST data if submitted.
     *
     * @return bool True if the form is submitted; otherwise, false.
     */
    public function isSubmitted(): bool
    {
        return $this->submitted;
    }

    /**
     * Check if the form submission is trusted based on nonces.
     *
     * @return bool True if the submission is trusted; otherwise, false.
     */
    public function isTrusted(): bool
    {
        return $this->trusted;
    }

    /**
     * Add a security check input then generete FORM HTML
     *
     * @return string The generated HTML Form element
     */
    public function getHTML(bool $indent = false): string
    {
        $this->secureForm();
        return parent::getHTML($indent);
    }

    /**
     * Get the URL associated with a page name from the configuration.
     *
     * @param string $pagename The name of the page.
     *
     * @return string|null The URL associated with the page name, or null if not found.
     */
    public function getRouteUrl(string $pagename): ?string
    {
        $page = Udash::instance()->getConfig($pagename);
        if(is_array($page) && array_key_exists('route', $page)) {
            $path = ROOT_DIR . "/" . Uss::instance()->filterContext($page['route']);
            return Uss::instance()->getUrl($path, true);
        };
    }

    /**
     * Set a report message for a form field.
     *
     * @param string $name The name of the form field.
     * @param string $message The report message.
     * @param string $class The CSS class for styling the report.
     *
     * @return void
     */
    public function setReport(string $name, string $message, string $class = 'text-danger fs-12px'): void
    {
        $fieldset = $this->getFieldset($name);
        if($fieldset) {
            $fieldset['report']->setContent("* {$message}");
            $fieldset['report']->addAttributeValue('class', $class);
        };
    }

    protected function onFormBuild()
    {

    }

    /**
     * Secure the form by adding nonce fields.
     *
     * @return void
     */
    private function secureForm(): void
    {
        if(!$this->secured) {
            // add a nonce
            $this->add('udf-hash', UssForm::INPUT, UssForm::TYPE_HIDDEN, [
                'value' => $this->getAttribute('name') . "/" . Uss::instance()->nonce($this->nonceKey)
            ]);
            // mark as secured!
            $this->secured = true;
        }
    }

    /**
     * Filter and sanitize the values in the $_POST array.
     *
     * This method trims whitespace from all POST values and removes specific
     * keys from the $_POST array, such as 'udf-name' and 'udf-hash'.
     *
     * @return void
     */
    private function initForm()
    {

        $name = $this->getAttribute('name');
        $method = $this->getAttribute('method');

        $this->nonceKey = $name . ':' . $method;

        // Check if request method matches
        if($_SERVER['REQUEST_METHOD'] === $method) {

            // Get submitted data by reference
            if($method === 'POST') {
                $data = &$_POST;
            } else {
                $data = &$_GET;
            }

            $hash = explode('/', $data['udf-hash'] ?? '');

            if(count($hash) === 2) {

                list($name, $nonceValue) = $hash;

                // Check if form is submitted
                $this->submitted = ($name === $this->getAttribute('name'));

                /**
                 * Check if it is trusted
                 * This also requires checking for CSRF Attack and other potential hack attempt
                 */
                $this->trusted = $this->submitted && Uss::instance()->nonce($this->nonceKey, $nonceValue);
            }

        };

    }

    private function formulateError($method = null, string $error)
    {
        $childClass = get_called_class();
        throw new \Exception($method . " must be overridden on `$childClass` " . $error);
    }

}
