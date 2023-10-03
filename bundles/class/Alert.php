<?php

class Alert
{
    protected string $type = 'modal'; // notification
    protected string $library = 'bootbox';
    protected array $options = [];
    protected string $alertId;
    protected bool $followRedirect = false;

    private static $liberated = false;

    public function __construct(?string $message = null)
    {
        $this->alertId = uniqid();
        $this->setOption('message', $message);
    }

    /**
     * This method is called only once by
     * UdTwigExtension Class Instance
     */
    public static function flushAll()
    {
        if(!self::$liberated) {
            $alerts = $_SESSION['UssAlert'] ?? [];
            foreach(array_keys($alerts) as $key) {
                $data = $alerts[$key];
                if(is_array($data)) {
                    $alert = (new self())
                        ->type($data['type'])
                        ->setOption($data['options']);
                    $alert->display($data['method'], $data['delay']);
                }
                unset($_SESSION['UssAlert'][$key]);
            };
            self::$liberated = true;
        }
        // Garbage Collection
        if(empty($_SESSION['UssAlert'])) {
            unset($_SESSION['UssAlert']);
        };
    }

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

    public function type(string $type): self
    {
        $types = [
            'modal' => 'bootbox',
            'notification' => 'izitoast'
        ];
        $key = strtolower(trim($type));
        if(!in_array($key, array_keys($types))) {
            $acceptedTypes = implode(", ", array_keys($types));
            throw new \Exception(__METHOD__ . "() Invalid Type (`{$type}`) given as argument, type can be one of {$acceptedTypes}");
        };
        $this->type = $key;
        $this->library = $types[$key];
        return $this;
    }

    /**
     * IMPORTANT:
     * FOLLOW REDIRECT WILL NOT WORK IF YOU REDIRECT AND DO NOT EXIT THE SCRIPT
     */
    public function followRedirectAs($name): self
    {
        if(!is_string($name)) {
            $name = uniqid();
        }
        $this->alertId = $name;
        $this->followRedirect = true;
        return $this;
    }

    public function display(?string $method = null, ?int $delay = 0): void
    {
        if($this->type === 'modal') {
            $jsResult = $this->bootBox($method);
        } else {
            $jsResult = $this->iziToast($method);
        }

        $delay = abs($delay ?? 0);

        if($this->followRedirect) {
            $_SESSION['UssAlert'] = $_SESSION['UssAlert'] ?? [];
            $vars = get_object_vars($this);
            $vars['method'] = $method;
            $vars['delay'] = $delay;
            $_SESSION['UssAlert'][$this->alertId] = $vars;
        };

        if(!empty($delay)) {
            $jsResult = "setTimeout(function() { " . $jsResult . " }, {$delay})";
        };

        UssTwigBlockManager::instance()->appendTo('__alert', $this->alertId, $jsResult);
    }

    protected function bootBox($method): string
    {
        $method = $this->tryMethod($method, ['alert', 'dialog']);

        $options = $this->getDefaultOptions([
            'message',
            in_array($method, ['confirm', 'prompt']) ? 'callback' : null
        ]);

        $options += [
            'title' => Uss::instance()->options->get('web:title')
        ];

        $snippet = "bootbox.{$method}(" . $this->jsEncode($options) . ");";

        return $snippet;
    }

    protected function iziToast($method)
    {
        $method = $this->tryMethod($method, ['info', 'success', 'warning', 'error', 'question']);

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

    protected function tryMethod(?string $method, array $methods)
    {
        $method = trim($method) ?: $methods[0];
        if(!in_array($method, $methods)) {
            $acceptedMethods = '[' . implode(", ", $methods) . ']';
            $class = __CLASS__;
            throw new \Exception(
                "{$class}::display('{$method}', ...): Unaccepted `{$this->type}` prompt used in (#argument 1), try one of the following: {$acceptedMethods}"
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
        return $this->options + [

        ];
    }

    protected function jsEncode(array $options): string
    {
        $options = json_encode($options);
        $jsReverse = "JSON.parse(`" . $options . "`)";
        return $jsReverse;
    }

}
