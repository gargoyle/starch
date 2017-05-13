<?php

namespace Starch\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Delegate implements DelegateInterface
{
    /**
     * @var callable
     */
    private $callable;

    /**
     * @param callable $callable
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    /**
     * @param  ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request) : ResponseInterface
    {
        return ($this->callable)($request);
    }
}
