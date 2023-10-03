<?php

class UrlGenerator
{
    private string $path;
    private array $query = [];

    public function __construct(string $path = '/', array $param = [])
    {
        $this->init($path);
        $this->query = array_merge($this->query, $param);
    }

    public function __toString()
    {
        return $this->getResult();
    }

    public function setParam(string $key, ?string $value): self
    {
        $this->query[$key] = $value;
        return $this;
    }

    public function removeParam(string $key): self
    {
        if(isset($this->query[$key])) {
            unset($this->query[$key]);
        }
        return $this;
    }

    public function getResult()
    {
        $uss = Uss::instance();
        $result = $uss->getUrl(ROOT_DIR . "/" . Ud::ROUTE);
        if(!empty($this->path)) {
            $result .= "/{$this->path}";
        };
        if(!empty($this->query)) {
            $result .= "?" . http_build_query($this->query);
        };
        return $result;
    }

    private function init(string $path): void
    {
        $path = explode("?", $path);
        $uss = Uss::instance();
        $this->path = $uss->filterContext($path[0]);
        if(!empty($path[1])) {
            parse_str($path[1], $this->query);
        };
    }

};
