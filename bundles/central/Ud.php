<?php

use Ucscode\SQuery\SQuery;

final class Ud extends AbstractUd
{
    use SingletonTrait;

    /**
     * Dashboard Route
     *
     * The default dashboard route to asses udash
     */
    public const ROUTE = 'dashboard';

    /** @ignore */
    private bool $firewallEnabled = true;
    private bool $isRecursiveRender = false;

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
                $group = array_filter($this->configs, function ($value, $key) use ($property) {
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
    public function removeConfig(string $property): void
    {
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
    public function enableFirewall(bool $enable = true): void
    {
        $this->firewallEnabled = $enable;
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
    public function render(string $template, array $options = []): void
    {
        $user = new User();

        if(!$user->getFromSession() && $this->firewallEnabled) {
            $this->activateLoginPage($user, $template, $options);
        };

        $options['user'] = $user;

        Uss::instance()->render($template, $options);
    }

    /**
     * Perform a simple database query to retrieve a single row based on a specified value and column.
     *
     * This method constructs and executes a SELECT query to fetch a single row from the specified database table
     * where the specified column matches the provided value.
     *
     * @param string $tableName   The name of the database table to query.
     * @param string $value       The value to match in the specified column.
     * @param string $columnName  (Optional) The name of the column to search for the specified value. Default is 'id'.
     *
     * @return array|null         An associative array representing the fetched row, or null if no matching row is found.
     */
    public function fetchData(string $tableName, null|int|string|array $value, $columnName = 'id')
    {
        $parameter = is_array($value) ? $value : $columnName;

        $SQL = (new SQuery())->select()
            ->from($tableName)
            ->where($parameter, $value);

        $result = Uss::instance()->mysqli->query($SQL);

        return $result->fetch_assoc();
    }

    /**
    * Generates a URL based on the provided path and parameters.
    *
    * @param string $path The path for the URL.
    * @param array $param An associative array of query parameters.
    *
    * @return object An anonymous class representing the generated URL with methods to manipulate parameters.
    *               - setParam(string $key, ?string $value): Set or update a query parameter.
    *               - removeParam(string $key): Remove a query parameter.
    *               - getResult(): Get the final generated URL as a string.
    */
    public function urlGenerator(string $path = '/', array $param = []): object
    {
        return new UrlGenerator($path, $param);
    }

    public function getArchive(string $pageName): ?UdArchive
    {
        $defaultPages = array_values(array_filter($this->defaultPages, function ($page) use ($pageName) {
            return $page->name === $pageName;
        }));
        return $defaultPages[0] ?? null;
    }

    public function removeArchive(string $pageName): null|bool
    {
        $page = $this->getArchive($pageName);
        if($page) {
            if($page->name === 'login') {
                throw new \Exception(
                    sprintf(
                        "%s Error: Default login page can only be modified but cannot be removed; Please make changes to the page attributes instead",
                        __METHOD__
                    )
                );
            }
            $key = array_search($page, $this->defaultPages, true);
            if($key !== false) {
                unset($this->defaultPages[$key]);
            };
        };
        return null;
    }

    /**
     * Get the URL associated with a page name from the configuration.
     *
     * @param string $pageName The name of the page.
     *
     * @return string|null The URL associated with the page name, or null if not found.
     */
    public function getArchiveUrl(string $pageName): ?string
    {
        $ud = Ud::instance();
        $page = $ud->getArchive($pageName);
        if(!$page || is_null($page->get('route'))) {
            return null;
        }
        $urlGenerator = new UrlGenerator($page->get('route'));
        return $urlGenerator->getResult();
    }

    /**
     * Activate login page
     *
     * Login page do not need controller or route.
     * The login page will automatically appear on any route once the user is not authorized
     * Unless the firewall is disabled before the render method is called
     */
    private function activateLoginPage(User &$user, string &$template, array &$options): void
    {
        $loginPage = $this->getArchive(UdArchive::LOGIN);

        // Get login form and handles submission
        $options['form'] = new ($loginPage->get('form'))(UdArchive::LOGIN);
        $options['form']->handleSubmission();

        // After form submission has been handled, checks again if user is authorized
        if(!$user->getFromSession()) {
            // If not, display login page
            $template = $loginPage->get('template');
        }
    }

}