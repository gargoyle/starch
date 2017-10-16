<?php

namespace Starch\Tests\Unit\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use PHPUnit_Framework_MockObject_MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Starch\Middleware\ClosureMiddleware;
use PHPUnit\Framework\TestCase;

class ClosureMiddlewareTest extends TestCase
{
    public function testCallsClosureWithRequestAndDelegate()
    {
        /** @var ServerRequestInterface|PHPUnit_Framework_MockObject_MockObject $mockRequest */
        $mockRequest = $this->createMock(ServerRequestInterface::class);
        /** @var DelegateInterface|PHPUnit_Framework_MockObject_MockObject $mockDelegate */
        $mockDelegate = $this->createMock(DelegateInterface::class);
        $mockDelegate->method('process')
            ->willReturn($this->createMock(ResponseInterface::class));

        $middleware = new ClosureMiddleware(function(ServerRequestInterface $request, DelegateInterface $delegate) use ($mockRequest, $mockDelegate) {
            $this->assertEquals($mockRequest, $request);
            $this->assertEquals($mockDelegate, $delegate);

            return $delegate->process($request);
        });

        $middleware->process($mockRequest, $mockDelegate);
    }
}
