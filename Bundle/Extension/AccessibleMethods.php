<?php

namespace Module\Dashboard\Bundle\Extension;

use Module\Dashboard\Bundle\Kernel\Interface\DashboardInterface;

class AccessibleMethods
{
    protected array $accessibleMethods = [
        'getDocument',
        'getDocuments',
        'urlGenerator',
        'isFirewallEnabled',
        'getTheme',
    ];

    public function __construct(protected DashboardInterface $dashboard)
    {
    }

    public function __call(string $originalMethod, mixed $args): mixed
    {
        $convention = $this->composeMethodConvention($originalMethod);
        
        foreach($convention as $method) {

            $isAccessible = in_array(
                strtolower($method), 
                array_map('strtolower', $this->accessibleMethods)
            );

            if ($isAccessible && method_exists($this->dashboard, $method)) {
                return call_user_func_array([$this->dashboard, $method], $args);
            }
        }

        if(method_exists($this->dashboard, $originalMethod)) {
            throw new \RuntimeException(
                "Call to method `{$originalMethod}` is not allowed within twig templates."
            );
        }
    }

    protected function composeMethodConvention(string $method): array
    {
        $convention = [null, 'get', 'is', 'has'];
        array_walk($convention, function(&$value) use ($method) {
            $value = $value === null ? $method : $value . ucfirst($method);
        });
        return $convention;
    }
}
