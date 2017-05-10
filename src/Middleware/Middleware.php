<?php

namespace Starch\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Middleware
{
    /**
     * @var callable
     */
    private $handler;

    /**
     * @var callable
     */
    private $next;

    /**
     * @param callable $handler
     * @param callable $next
     */
    public function __construct(callable $handler, callable $next)
    {
        $this->handler = $handler;
        $this->next = $next;
    }

    /**
     * @param  callable $next
     * @return void
     */
    public function setNext(callable $next) : void
    {
        $this->next = $next;
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response)
    {
        $handler = $this->handler;

        return $handler($request, $response, $this->next);
    }
}
