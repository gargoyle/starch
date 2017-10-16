<?php

namespace Starch\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LastDelegate implements DelegateInterface
{
    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request): ResponseInterface
    {
        throw new \LogicException("The last Middleware in the Stack can not call \$delagate->process()");
    }
}
