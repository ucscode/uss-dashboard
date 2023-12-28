<?php

namespace Module\Dashboard\Bundle\Alert;

class Alert implements AlertInterface
{
    protected const ALERT_TYPES = [
        'modal' => 'bootbox',
        'notification' => 'izitoast'
    ];

    protected array $options = [];
    protected string $type = 'modal';
    protected string $library = self::ALERT_TYPES['modal'];
    protected string $alertId;
    protected bool $followRedirect = false;
    private static $liberated = false;

    public function __construct(?string $message = null)
    {
        $this->alertId = uniqid();
        $this->setOption('message', $message);
    }

    /**
     * Set Alert Type (modal|notification): Default = 'modal'
     * @return self
     */
    public function type(string $type): self
    {
        $key = strtolower(trim($type));
        $availTypes = array_keys(self::ALERT_TYPES);

        if(!in_array($key, $availTypes)) {
            throw new \Exception(
                sprintf(
                    "%s() Invalid Type (`%s`) given as argument, type can be one of %s",
                    __METHOD__,
                    $type,
                    implode(", ", array_keys($availTypes))
                )
            );
        };

        $this->type = $key;
        return $this;
    }

    /**
     * Set Alert Options; E.g Message, Position etc
     * @return self
     */
    public function setOption(string|array $option, $value = null): self
    {
        if(is_string($option)) {
            $option = [$option => $value];
        };
        foreach($option as $key => $value) {
            $this->options[$key] = $value;
        };
        return $this;
    }

    /**
     * Display alert even when redirect occurs
     *
     * Note: This method will not work if you redirect and do not terminate the script
     * You should call on exit() or die() to terminate the script
     */
    public function followRedirectAs(string $name = null): self
    {
        if(!is_string($name)) {
            $name = uniqid();
        }
        $this->alertId = $name;
        $this->followRedirect = true;
        return $this;
    }

    /**
     * Display the alert
     * @return void
     */
    public function display(?string $method = null, int $delay = 0): void
    {
        $delay = abs($delay);
        $javascriptOutput = ($this->type === 'modal') ? $this->bootBox($method) : $this->iziToast($method);

        if($this->followRedirect) {
            $this->preserveAlertSession($method, $delay);
        };

        if(!empty($delay)) {
            $javascriptOutput = sprintf(
                "%s %s %s",
                "setTimeout(() => {",
                $javascriptOutput,
                "}, {$delay})"
            );
        };

        BlockManager::instance()->appendTo(
            self::SESSID,
            $this->alertId,
            $javascriptOutput
        );
    }

    /**
     * This method is called only once by
     * UdTwigExtension Class Instance
     */
    public static function exportContent()
    {
        if(!self::$liberated) {
            $alerts = $_SESSION[self::SESSID] ?? [];
            foreach($alerts as $key => $data) {
                if(is_array($data)) {
                    $alert = (new self())
                        ->type($data['type'])
                        ->setOption($data['options'])
                        ->display($data['method'], $data['delay']);
                }
                unset($_SESSION[self::SESSID][$key]);
            };
            self::$liberated = true;
        }
    }

    protected function bootBox($method): string
    {
        $method = $this->validateMethod($method, [
            'alert',
            'dialog'
        ]);

        $options = $this->getDefaultOptions([
            'message',
            in_array($method, ['confirm', 'prompt']) ? 'callback' : null
        ]);

        $options += [
            'title' => Uss::instance()->options->get('company:name')
        ];

        $snippet = "bootbox.{$method}(" . $this->jsEncode($options) . ");";

        return $snippet;
    }

    protected function iziToast($method)
    {
        $method = $this->validateMethod($method, [
            'info',
            'success',
            'warning',
            'error',
            'question'
        ]);

        $options = $this->getDefaultOptions([
            'message'
        ]);

        $options += [
            'position' => 'topRight',
            'timeout' => 6000
        ];

        if(array_key_exists('title', $options) && is_null($options['title'])) {
            unset($options['title']);
        }

        $snippet = "iziToast.{$method}(" . $this->jsEncode($options) . ");";

        return $snippet;
    }

    protected function validateMethod(?string $method, array $methods): string
    {
        $method = trim($method) ?: $methods[0];
        if(!in_array($method, $methods)) {
            throw new \Exception(
                sprintf(
                    "%s::display('%s', ...): Unaccepted `%s` prompt used in (#argument 1), try one of the following: %s",
                    __CLASS__,
                    $method,
                    $this->type,
                    '[' . implode(", ", $methods) . ']'
                )
            );
        }
        return $method;
    }

    protected function getDefaultOptions(array $required): array
    {
        foreach($required as $key) {
            if(!empty($key)) {
                if(empty($this->options[$key])) {
                    throw new \Exception(
                        ucfirst("{$this->type} Alert Error: \"{$key}\" option is required")
                    );
                }
            }
        };
        // The user defined options + any missing option generally required for all alert types
        return $this->options + [];
    }

    protected function jsEncode(array $options): string
    {
        $options = json_encode($options);
        $jsReverse = "JSON.parse(atob(`" . base64_encode($options) . "`))";
        return $jsReverse;
    }

    protected function preserveAlertSession($method, $delay): void
    {
        $_SESSION[self::SESSID] = $_SESSION[self::SESSID] ?? [];
        $properties = get_object_vars($this);
        $properties['method'] = $method;
        $properties['delay'] = $delay;
        $_SESSION[self::SESSID][$this->alertId] = $properties;
    }

}
