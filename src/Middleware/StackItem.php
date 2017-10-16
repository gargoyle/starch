<?php

namespace Starch\Middleware;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Starch\Router\Route;

class StackItem
{
    /**
     * @var MiddlewareInterface
     */
    private $middleware;

    /**
     * @var string|null
     */
    private $pathConstraint;

    public function __construct(MiddlewareInterface $middleware, string $pathConstraint = null)
    {
        $this->middleware = $middleware;
        $this->pathConstraint = $pathConstraint;
    }

    /**
     * @return MiddlewareInterface
     */
    public function getMiddleware(): MiddlewareInterface
    {
        return $this->middleware;
    }

    /**
     * Checks if this item should be executed for a given request
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
