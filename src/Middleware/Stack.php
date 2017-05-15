<?php

namespace Starch\Middleware;

use DI\Container;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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

    public function add($middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    public function resolve(ServerRequestInterface $request): ResponseInterface
    {
        $delegate = $this->getDelegate();

        return $delegate->process($request);
    }

    /**
     * @return Delegate
     */
    private function getDelegate()
    {
        $middleware = array_shift($this->middlewares);

        if (null === $middleware) {
            return new Delegate(function(ServerRequestInterface $request) {
                $params = [$request] + $request->getAttribute('vars');

                return $this->container->call($request->getAttribute('handler'), $params);
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
