<?php

namespace Starch\Router;

class Route
{
    private $methods;
    private $route;
    private $handler;

    public function __construct(array $methods, string $route, $handler)
    {
        $this->methods = array_map('strtoupper', $methods);
        $this->route = $route;
        $this->handler = $handler;
    }

    public function getMethods() : array
    {
        return $this->methods;
    }

    public function getRoute() : string
    {
        return $this->route;
    }

    public function getHandler()
    {
        return $this->handler;
    }
}
