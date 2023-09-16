<?php

use Ucscode\Packages\Pairs;

final class Udash extends AbstractUdash
{
    use SingletonTrait;
    use EncapsulatedPropertyAccessTrait;

    /**
     * Dashboard Route
     *
     * The default dashboard route to asses udash
     */
    public const ROUTE = '/dashboard';

    #[Accessible]
    protected $user;

    #[Accessible]
    protected $usermeta;

    /** @ignore */
    private bool $hasActiveFirewall = true;

    /**
     * Set a configuration property to a given value.
     *
     * @param string $property The name of the property to set.
     * @param mixed $value The value to assign to the property.
     *
     * @return bool True if the configuration property was successfully set, false otherwise.
     */
    public function setConfig(?string $property = null, mixed $value = null): bool
    {
        $this->configs[$property] = $value;
        return isset($this->configs[$property]);
    }

    /**
     * Get a configuration property's value.
     *
     * @param string|null $property The name of the property to retrieve. If null, returns the entire configuration.
     * @param bool $group If true, retrieve an array of configurations matching the property name; otherwise, get the value of the specified property.
     *
     * @return mixed The value of the configuration property or the entire configuration group.
     */
    public function getConfig(?string $property = null, bool $group = false): mixed
    {
        if(func_num_args() === 0) {
            return $this->configs;
        } else {
            if($group) {
                $group = array_filter($this->configs, function ($value, $key) use($property) {    
                    $property = str_replace("/", "\\/", $property);
                    return preg_match("/^" . $property . "/", $key);
                }, ARRAY_FILTER_USE_BOTH);
                return $group;
            }
            return $this->configs[$property] ?? null;
        };
    }

    /** Remove a configuration property.
     *
     * @param string $property The name of the property to remove.
     *
     * @return void
     */
    public function removeConfig(string $property): void {
        if(isset($this->configs[$property])) {
            unset($this->configs[$property]);
        };
    }

    /**
     * Enable or disable a firewall. I.E Prevent or allow unauthorized user to access a page
     *
     * @param bool $enable True to enable the firewall, false to disable it. Defaults to true.
     *
     * @return void
     */
    public function enableFirewall(bool $enable = true): void {
        $this->hasActiveFirewall = $enable;
    }
    
    /**
     * Render a template with optional rendering options and a Twig block manager.
     *
     * @param string $template The name or path of the template to render.
     * @param array $options An array of rendering options.
     * @param UssTwigBlockManager|null $ussTwigBlockManager A Twig block manager instance (optional).
     *
     * @return void
     */
    public function render(string $template, array $options = [], ?UssTwigBlockManager $ussTwigBlockManager = null): void
    {
        if($this->hasActiveFirewall && empty($this->user)) {
            $template = $this->getConfig('templates:login');
            $options['form'] = $this->getConfig('forms:login');
        };
        $options['user'] = $this->user;
        $options['usermeta'] = $this->usermeta;
        Uss::instance()->render($template, $options, $ussTwigBlockManager);
    }

    /**
     * Configure the user settings.
     *
     * @return void
     */
    protected function configureUser(): void
    {
        $prefix = DB_PREFIX;
        $this->usermeta = new Pairs(Uss::instance()->mysqli, DB_PREFIX . "usermeta");
        $this->usermeta->linkParentTable("{$prefix}_users", DB_PREFIX . "users");
        // {{{{{ As well as the user sponsor (upline) when registering }}}}}
    }

}