<?php

class UrlGenerator
{
    private string $path;
    private string $base;
    private array $query = [];

    public function __construct(string $path = '/', array $param = [], ?UdInterface $baseInterface = null)
    {
        $this->init($path);
        $this->query = array_merge($this->query, $param);
        $this->setBase($baseInterface);
    }

    public function __toString()
    {
        return $this->getResult();
    }

    public function setBase(?UdInterface $baseInterface): self
    {
        if(empty($baseInterface)) {
            $baseInterface = Ud::instance();
        }      
        $this->base = $baseInterface->base;
        return $this;
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
        $result = $uss->abspathToUrl(ROOT_DIR . "/" . $this->base);
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
