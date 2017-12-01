<?php

namespace Starch\Middleware;

use Interop\Http\Server\RequestHandlerInterface;
use Invoker\InvokerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Delegate implements RequestHandlerInterface
{
    /**
     * @var StackItem
     */
    private $item;

    /**
     * @var RequestHandlerInterface
     */
    private $next;

    /**
     * @var InvokerInterface
     */
    private $invoker;

    /**
     * @param StackItem $item
     * @param RequestHandlerInterface $next
     * @param InvokerInterface $invoker
     */
    public function __construct(StackItem $item, RequestHandlerInterface $next, InvokerInterface $invoker)
    {
        $this->item = $item;
        $this->next = $next;
        $this->invoker = $invoker;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->item->executeFor($request->getAttribute('route'))) {
            return $this->invoker->call([$this->item->getMiddleware(), 'process'], [$request, $this->next]);
        }

        return $this->next->handle($request);
    }
}
