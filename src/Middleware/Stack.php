<?php

namespace Starch\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Invoker\InvokerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Starch\Router\Route;

class Stack implements StackInterface
{
    /**
     * @param InvokerInterface
     */
    private $invoker;

    /**
     * @var MiddlewareInterface[]
     */
    private $middlewares = [];

    public function __construct(InvokerInterface $invoker)
    {
        $this->invoker = $invoker;
    }

    /**
     * Add a middleware to the stack
     *
     * @param MiddlewareInterface $middleware
     */
    public function add(MiddlewareInterface $middleware) : void
    {
        $this->middlewares[] = $middleware;
    }

    /**
     * Get the first Delegate in the stack and call it to start the chain.
     *
     * @param  ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function resolve(ServerRequestInterface $request) : ResponseInterface
    {
        $delegate = $this->getDelegate();

        return $delegate->process($request);
    }

    /**
     * Returns a Delegate that has a callable to call the next middleware
     *
     * If the stack is empty, it will return a Delegate that will call the route handler
     *
     * @return DelegateInterface
     */
    private function getDelegate() : DelegateInterface
    {
        $middleware = array_shift($this->middlewares);

        if (null === $middleware) {
            return new Delegate(function(ServerRequestInterface $request) {
                /** @var Route $route */
                $route = $request->getAttribute('route');
                $params = [$request] + $route->getArguments();

                return $this->invoker->call($route->getHandler(), $params);
            });
        }

        return new Delegate(function (ServerRequestInterface $request) use ($middleware) {
            $result = $this->invoker->call([$middleware, 'process'], [$request, $this->getDelegate()]);

            return $result;
        });

    }
}
