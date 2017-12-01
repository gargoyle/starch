<?php

namespace Starch\Middleware;

use Closure;
use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ClosureMiddleware implements MiddlewareInterface
{
    /**
     * @var Closure
     */
    private $closure;

    public function __construct(Closure $closure)
    {
        $this->closure = $closure;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return ($this->closure)($request, $handler);
    }
}
