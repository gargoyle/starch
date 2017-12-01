<?php

namespace Starch\Middleware;

use Interop\Http\Server\RequestHandlerInterface;
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
    public function add(StackItem $item): void
    {
        $this->items[] = $item;
    }

    /**
     * Get the first Delegate in the stack and call it to start the chain.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function resolve(ServerRequestInterface $request): ResponseInterface
    {
        $delegate = $this->getDelegate();

        return $delegate->handle($request);
    }

    /**
     * Returns a Delegate that has a callable to call the next middleware
     *
     * If the stack is empty, it will return a Delegate that will throw an exception
     * The last item in the stack must not call the next Delegate but rather just return a Response on it's own
     *
     * @return RequestHandlerInterface
     */
    private function getDelegate(): RequestHandlerInterface
    {
        $item = array_shift($this->items);

        if (null === $item) {
            return new LastDelegate();
        }

        return new Delegate($item, $this->getDelegate(), $this->invoker);
    }
}
