<?php

namespace Starch\Middleware;

use Interop\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LastDelegate implements RequestHandlerInterface
{
    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        throw new \LogicException("The last Middleware in the Stack can not call \$handler->handle()");
    }
}
