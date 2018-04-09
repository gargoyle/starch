<?php

namespace Starch\Router;

use Psr\Http\Server\RequestHandlerInterface;

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
     * @var RequestHandlerInterface|string
     */
    private $handler;

    public function __construct(array $methods, string $path, $handler)
    {
        $this->methods = array_map('strtoupper', $methods);
        $this->path = $path;
        $this->handler = $handler;
    }

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
}
