<?php

namespace Starch\Middleware;

use DI\Container;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

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

    public function resolve(ServerRequestInterface $request, $final): ResponseInterface
    {
        $delegate = $this->getDelegate(0, $final);

        return $delegate->process($request);
    }

    /**
     * @param int $index middleware stack index
     *
     * @return Delegate
     */
    private function getDelegate($index, $final)
    {
        if (!isset($this->middlewares[$index])) {
            return new Delegate(function(ServerRequestInterface $request) use ($final) {
                return $this->container->call($final, [$request]);
            });
        }

        return new Delegate(function (ServerRequestInterface $request) use ($index, $final) {
            $callable = $this->middlewares[$index];
            if (is_string($callable)) {
                $service =  $this->container->get($callable);
                if ($service instanceof MiddlewareInterface) {
                    $callable = [$callable, 'process'];
                }
            }

            $result = $this->container->call($callable, [$request, $this->getDelegate($index + 1, $final)]);

            return $result;
        });

    }
}
