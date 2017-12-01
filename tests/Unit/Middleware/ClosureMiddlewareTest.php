<?php

namespace Starch\Tests\Unit\Middleware;

use Interop\Http\Server\RequestHandlerInterface;
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
        /** @var RequestHandlerInterface|PHPUnit_Framework_MockObject_MockObject $mockDelegate */
        $mockDelegate = $this->createMock(RequestHandlerInterface::class);
        $mockDelegate->method('handle')
            ->willReturn($this->createMock(ResponseInterface::class));

        $middleware = new ClosureMiddleware(function(ServerRequestInterface $request, RequestHandlerInterface $handler) use ($mockRequest, $mockDelegate) {
            $this->assertEquals($mockRequest, $request);
            $this->assertEquals($mockDelegate, $handler);

            return $handler->handle($request);
        });

        $middleware->process($mockRequest, $mockDelegate);
    }
}
