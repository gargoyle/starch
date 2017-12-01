<?php

namespace Starch\Router;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Invoker\InvokerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RouterMiddleware implements MiddlewareInterface
{
    /**
     * @var InvokerInterface
     */
    private $invoker;

    public function __construct(InvokerInterface $invoker)
    {
        $this->invoker = $invoker;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Route $route */
        $route = $request->getAttribute('route');
        $params = [$request] + $route->getArguments();

        return $this->invoker->call($route->getHandler(), $params);
    }
}
