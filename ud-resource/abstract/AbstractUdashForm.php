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
     * The URL to redirect to upon successful form submission.
     *
     * @var string
     */
    protected ?string $redirectUrl = null;

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
            # Create Url From Route;
            $action = $_SERVER['REQUEST_URI'];
        }

        parent::__construct($name, $action, $method, $enctype);

        $this->initForm();

        // Build the form;
        $this->buildForm();
    }

    public function handleSubmission(): self
    {
        if($this->isSubmitted()) {

            if($this->isTrusted()) {

                $post = $this->getFilteredData();

                if($this->isValid($post)) {
                    
                    $post = $this->prepareEntryData($post);

                    $this->saveToDatabase($post);

                } else {
                    
                    $this->populate($post);

                };

            }; // !Trust

        }; // !Submit

        return $this;
    }

    protected function saveToDataBase(array $data) 
    {
        $this->onDataEntryFailure($data);
    }

    public function onDataEntryFailure(array $data, bool $isUpdate = false)
    {
        // Extend to apply your logics
    }

    public function onDataEntrySuccess(array $data, bool $isUpdate = false)
    {
        // Extend to apply your logics
    }

    public function isValid(?array $post): bool
    {
        if($post === null) {
            $post = $this->getFilteredData();
        };
        return empty($post);
    }

    /**
     *
     */
    public function getFilteredData(bool $sanitize = true): array
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
            return Core::url($path, true);
        };
    }

    /**
     * Set the URL to redirect to upon successful form submission.
     *
     * @param string $path The URL to redirect to.
     *
     * @return void
     */
    public function redirectOnSuccessTo(string $location): void
    {
        $this->redirectUrl = $location;
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
     * Get the data to be persisted to the database.
     *
     * This method retrieves and prepares the data that is intended to be saved or persisted in the database.
     * It may involve data manipulation or transformation before the data is ready for database storage.
     * It is available to child classes that inherits the AbstractUdashForm
     *
     * @param array $data The original data to be processed and persisted.
     *
     * @return array The prepared data ready for database storage.
     */
    protected function prepareEntryData(array $data): array
    {
        return $data;
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
    protected function setReport(string $name, string $message, string $class = 'text-danger fs-12px'): void
    {
        $fieldset = $this->getFieldset($name);
        if($fieldset) {
            $fieldset['report']->setContent("* {$message}");
            $fieldset['report']->addAttributeValue('class', $class);
        };
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

}
