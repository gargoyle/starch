<?php

namespace Starch\Middleware;

use Starch\Router\Route;

class Middleware
{
    /**
     * @var mixed
     */
    private $middleware;

    /**
     * @var string|null
     */
    private $pathConstraint;

    public function __construct($middleware, string $pathConstraint = null)
    {
        $this->middleware = $middleware;
        $this->pathConstraint = $pathConstraint;
    }

    /**
     * @return mixed
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }

    /**
     * Checks if this item should be executed for a given Route
     *
     * @param Route $route
     *
     * @return bool
     */
    public function executeFor(Route $route): bool
    {
        if (null === $this->pathConstraint) {
            return true;
        }

        return strpos($route->getPath(), $this->pathConstraint) === 0;
    }
}
