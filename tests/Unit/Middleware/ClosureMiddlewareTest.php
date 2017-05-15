<?php

namespace Starch\Tests\Unit\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Starch\Middleware\ClosureMiddleware;
use PHPUnit\Framework\TestCase;

class ClosureMiddlewareTest extends TestCase
{
    public function testCallsClosureWithRequestAndDelegate()
    {
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        $mockDelegate = $this->createMock(DelegateInterface::class);

        $middleware = new ClosureMiddleware(function($request, $delegate) use ($mockRequest, $mockDelegate) {
            $this->assertEquals($mockRequest, $request);
            $this->assertEquals($mockDelegate, $delegate);
        });

        $middleware->process($mockRequest, $mockDelegate);
    }
}
