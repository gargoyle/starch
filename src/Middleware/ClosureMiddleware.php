<?php

namespace Starch\Middleware;

use Closure;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;

class ClosureMiddleware implements MiddlewareInterface
{
    /**
     * @var Closure
     */
    private $closure;

    public function __construct(Closure $closure)
    {
        $this->closure = $closure;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        return ($this->closure)($request, $delegate);
    }
}
