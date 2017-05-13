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
        $delegate = $this->getDelegate(0);

        return $delegate->process($request);
    }

    /**
     * @param int $index middleware stack index
     *
     * @return Delegate
     */
    private function getDelegate($index)
    {
        if (!isset($this->middlewares[$index])) {
            return new Delegate(function(ServerRequestInterface $request) {
                return $this->container->call($request->getAttribute('handler'), [$request]);
            });
        }

        return new Delegate(function (ServerRequestInterface $request) use ($index) {
            $callable = $this->middlewares[$index];
            if (is_string($callable)) {
                $service =  $this->container->get($callable);
                if ($service instanceof MiddlewareInterface) {
                    $callable = [$callable, 'process'];
                }
            }

            $result = $this->container->call($callable, [$request, $this->getDelegate($index + 1)]);

            return $result;
        });

    }
}
