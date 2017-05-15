<?php

namespace Starch\Middleware;

use DI\Container;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Starch\Router\Route;

class Stack implements StackInterface
{
    /**
     * @param Container
     */
    private $container;

    /**
     * @var array
     */
    private $middlewares = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Add a middleware to the stack
     *
     * @param mixed $middleware
     */
    public function add($middleware) : void
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
     * Will leverage the container to either call a callable or a MiddlewareInterface
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

                return $this->container->call($route->getHandler(), $params);
            });
        }

        return new Delegate(function (ServerRequestInterface $request) use ($middleware) {
            if (is_string($middleware)) {
                $middleware = $this->container->get($middleware);
                if ($middleware instanceof MiddlewareInterface) {
                    $middleware = [$middleware, 'process'];
                }
            }

            $result = $this->container->call($middleware, [$request, $this->getDelegate()]);

            return $result;
        });

    }
}
