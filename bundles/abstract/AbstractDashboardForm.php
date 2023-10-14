<?php

use Ucscode\UssForm\UssForm;

abstract class AbstractDashboardForm extends UssForm implements DashboardFormInterface
{
    abstract protected function buildForm();

    protected array $style = [
        'label_class' => 'd-none',
        'required' => true,
        'column' => 'col-12 mb-2',
    ];

    private string $nonceKey;
    private string $hashKey = 'udf-hash';

    /**
     * A set of reusable data
     *
     * @var array
     */
    private array $links = [];

    public function __construct(
        string $name,
        ?string $action = null,
        string $method = 'POST',
        string $enctype = ''
    ) {
        parent::__construct($name, $action, $method, $enctype);
        $this->nonceKey = "{$name}:{$method}";
        $this->onCreate();
        $this->buildForm();
    }

    /**
     * @method onCreate
     * Child classes should provide their own implementation of this method.
     */
    protected function onCreate(): void
    {
        // @Requires Override
    }

    /**
     * This creates a dedicated process for handling form submission
     * @method handleSubmission
     * @return void
     */
    public function handleSubmission(): void
    {
        if($this->isSubmitted()) {

            if($this->isTrusted()) {

                $data = $this->extractRelevantData();

                if($this->isValid($data)) {
                    
                    $this->persistEntry($data) ? $this->onEntrySuccess($data) : $this->onEntryFailure($data);

                } else {
                    $this->handleInvalidRequest($data);
                };

            } else {
                $this->handleUntrustedRequest();
            }

        };
    }

    /**
     * @method isSubmitted
     * Child classes should provide their own implementation of this method.
     */
    public function isSubmitted(): bool
    {
        $hash = $this->getSecurityHash();
        if($hash) {
            return $hash['name'] === $this->getAttribute('name');
        };
        return false;
    }

    /**
     * @method isTrusted
     * Child classes should provide their own implementation of this method.
     */
    public function isTrusted(): bool
    {
        $hash = $this->getSecurityHash();
        if($hash) {
            return Uss::instance()->nonce($this->nonceKey, $hash['nonce']);
        }
        return false;
    }

    /**
     * @method extractRelevantData
     * Child classes should provide their own implementation of this method.
     */
    public function extractRelevantData(): array
    {
        $data = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
        return array_filter($data, function($value, $key) {
            return $key !== $this->hashKey;
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * @method isValid
     * Child classes should provide their own implementation of this method.
     */
    public function isValid(array $post): bool
    {
        return !empty($post);
    }

    /**
     * @method persistEntry
     * Child classes must provide their own implementation of this method.
     */
    public function persistEntry(array $data): bool
    {
        // @Requires Override
        $this->throwException(__METHOD__, 'to save entry into database');
    }

    /**
     * @method onEntryFailure
     * Child classes must provide their own implementation of this method.
     */
    public function onEntryFailure(array $data): void
    {
        $this->throwException(__METHOD__, 'to manage actions upon failure on database entry.');
    }

    /**
     * @method onEntrySuccess
     * Child classes must provide their own implementation of this method.
     */
    public function onEntrySuccess(array $data): void
    {
        $this->throwException(__METHOD__, 'to manage actions upon successful database entry.');
    }

    /**
     * @method handleUntrustedRequest
     * Child classes should provide their own implementation of this method.
     */
    public function handleUntrustedRequest(): void
    {
        // @Requires Override
    }

    /**
     * @method handleInvalidRequest
     * Child classes should provide their own implementation of this method.
     */
    public function handleInvalidRequest(?array $post): void
    {
        $this->populate($post);
    }

    /**
     * @method getHTML
     */
    public function getHTML(bool $indent = false): string
    {
        $this->setSecurityHash();
        return parent::getHTML($indent);
    }

    /**
     * Set a report message for a form field.
     *
     * @param string $name The name of the form field.
     * @param string $message The report message.
     * @param string $class The CSS class for styling the report.
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

    /**
     * @method setSecurityHash
     */
    private function setSecurityHash(): void
    {
        $name = $this->getAttribute('name');
        $nonce = Uss::instance()->nonce($this->nonceKey);

        $this->add(
            $this->hashKey,
            UssForm::INPUT,
            UssForm::TYPE_HIDDEN,
            ['value' => "{$name}/{$nonce}"]
        );
    }

    /**
     * @method getSecurityHash
     */
    protected function getSecurityHash(): ?array
    {
        $method = strtoupper($this->getAttribute('method'));

        if($_SERVER['REQUEST_METHOD'] === $method) {
            $data = ($method === 'POST') ? $_POST : $_GET;
            $hash = explode('/', $data[$this->hashKey] ?? '');
            if(count($hash) === 2) {
                return [
                    'name' => $hash[0],
                    'value' => $hash[1]
                ];
            }
        }
        
        return null;
    }

    /**
     * @method throwException
     */
    private function throwException($method = null, string $error)
    {
        throw new \Exception(
            sprintf(
                "%s must be overridden by `%s` %s",
                $method,
                get_called_class(),
                $error
            )
        );
    }

}
