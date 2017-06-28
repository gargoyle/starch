<?php

namespace Starch\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Invoker\InvokerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Delegate implements DelegateInterface
{
    /**
     * @var StackItem
     */
    private $item;

    /**
     * @var DelegateInterface
     */
    private $next;

    /**
     * @var InvokerInterface
     */
    private $invoker;

    /**
     * @param callable $callable
     */
    public function __construct(StackItem $item, DelegateInterface $next, InvokerInterface $invoker)
    {
        $this->item = $item;
        $this->next = $next;
        $this->invoker = $invoker;
    }

    /**
     * @param  ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request) : ResponseInterface
    {
        if ($this->item->executeFor($request->getAttribute('route'))) {
            return $this->invoker->call([$this->item->getMiddleware(), 'process'], [$request, $this->next]);
        }

        return $this->next->process($request);
    }
}
