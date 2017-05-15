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

    public function add($middleware) : void
    {
        $this->middlewares[] = $middleware;
    }

    public function resolve(ServerRequestInterface $request) : ResponseInterface
    {
        $delegate = $this->getDelegate();

        return $delegate->process($request);
    }

    /**
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
