<?php

namespace Starch\Middleware;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface StackInterface
{
    /**
     * Adds a middleware to the stack
     *
     * @param  MiddlewareInterface $middleware
     *
     * @return void
     */
    public function add(MiddlewareInterface $middleware) : void;

    /**
     * Moves a request through the stack to return a response
     *
     * @param  ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function resolve(ServerRequestInterface $request) : ResponseInterface;
}
