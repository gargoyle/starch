<?php

namespace Starch\Router;

class Route
{
    private $method;
    private $route;
    private $handler;

    public function __construct(string $method, string $route, callable $handler)
    {
        $this->method = $method;
        $this->route = $route;
        $this->handler = $handler;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function getHandler(): callable
    {
        return $this->handler;
    }
}
