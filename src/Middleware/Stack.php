<?php

namespace Starch\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Invoker\InvokerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Stack implements StackInterface
{
    /**
     * @param InvokerInterface
     */
    private $invoker;

    /**
     * @var StackItem[]
     */
    private $items = [];

    public function __construct(InvokerInterface $invoker)
    {
        $this->invoker = $invoker;
    }

    /**
     * Add a middleware to the stack
     *
     * @param StackItem $item
     */
    public function add(StackItem $item) : void
    {
        $this->items[] = $item;
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
        $delegate = $this->getDelegate($request);

        return $delegate->process($request);
    }

    /**
     * Returns a Delegate that has a callable to call the next middleware
     * Will skip over middlewares that shouldn't be executed for the request
     *
     * If the stack is empty, it will return a Delegate that will call the route handler
     *
     * @return DelegateInterface
     */
    private function getDelegate(ServerRequestInterface $request) : DelegateInterface
    {
        do {
            $item = array_shift($this->items);

            if (null === $item) {
                return new Delegate(function() {
                    throw new \LogicException("The last Middleware in the Stack can not call \$delagate->process()");
                });
            }
        } while (!$item->executeFor($request));

        $middleware = $item->getMiddleware();
        return new Delegate(function (ServerRequestInterface $request) use ($middleware) {
            $result = $this->invoker->call([$middleware, 'process'], [$request, $this->getDelegate($request)]);

            return $result;
        });

    }
}
