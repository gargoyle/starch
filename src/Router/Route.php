<?php

namespace Starch\Router;

class Route
{
    private $methods;
    private $path;
    private $handler;

    public function __construct(array $methods, string $path, $handler)
    {
        $this->methods = array_map('strtoupper', $methods);
        $this->path = $path;
        $this->handler = $handler;
    }

    public function getMethods() : array
    {
        return $this->methods;
    }

    public function getPath() : string
    {
        return $this->path;
    }

    public function getHandler()
    {
        return $this->handler;
    }
}
