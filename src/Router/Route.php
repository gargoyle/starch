<?php

namespace Starch\Router;

class Route
{
    /**
     * @var string[]
     */
    private $methods = [];

    /**
     * @var string
     */
    private $path;

    /**
     * @var array
     */
    private $arguments;

    /**
     * @var mixed
     */
    private $handler;

    public function __construct(array $methods, string $path, $handler)
    {
        $this->methods = array_map('strtoupper', $methods);
        $this->path = $path;
        $this->handler = $handler;
    }

    /**
     * @return string[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @param array $arguments
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;
    }
}
