<?php

namespace Starch\Middleware;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;

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
    public function getMiddleware() : MiddlewareInterface
    {
        return $this->middleware;
    }

    /**
     * Checks if this item should be executed for a given request
     *
     * @param  ServerRequestInterface $request
     *
     * @return bool
     */
    public function executeFor(ServerRequestInterface $request) : bool
    {
        if (null === $this->pathConstraint) {
            return true;
        }

        $regex = str_replace('/', '\\/', $this->pathConstraint); // Escape forward slashes with backslash
        $regex = '/^' . $regex . '$/'; // Add anchors

        $result = preg_match($regex, $request->getUri()->getPath());

        if (false === $result) {
            throw new InvalidArgumentException(sprintf(
                "The path constraint '%s' is not a valid regular expression (Error code: %d)",
                $this->pathConstraint,
                preg_last_error()
            ));
        }

        return (bool) $result;
    }
}
