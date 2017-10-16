<?php

namespace Starch\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface StackInterface
{
    /**
     * Adds a middleware to the stack
     *
     * @param StackItem $item
     *
     * @return void
     */
    public function add(StackItem $item): void;

    /**
     * Moves a request through the stack to return a response
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function resolve(ServerRequestInterface $request): ResponseInterface;
}
