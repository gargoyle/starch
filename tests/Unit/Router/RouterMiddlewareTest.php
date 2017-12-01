<?php

namespace Starch\Tests\Unit\Router;

use Interop\Http\Server\RequestHandlerInterface;
use Invoker\InvokerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Starch\Router\Route;
use Starch\Router\RouterMiddleware;
use PHPUnit\Framework\TestCase;

class RouterMiddlewareTest extends TestCase
{
    public function testItCallsInvokerWithCorrectArguments()
    {
        $route = $this->createMock(Route::class);
        $route->expects($this->once())
              ->method('getHandler')
              ->willReturn('foo');
        $route->expects($this->once())
              ->method('getArguments')
              ->willReturn(['foo' => 'bar']);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getAttribute')
                ->with($this->equalTo('route'))
                ->willReturn($route);

        $delegate = $this->createMock(RequestHandlerInterface::class);
        $delegate->expects($this->never())
            ->method('handle');

        $invoker = $this->createMock(InvokerInterface::class);
        $invoker->expects($this->once())
            ->method('call')
            ->with(
                $this->equalTo('foo'),
                $this->equalTo([
                    $request,
                    'foo' => 'bar'
                ])
            )
            ->willReturn($this->createMock(ResponseInterface::class));

        $middleware = new RouterMiddleware($invoker);
        $middleware->process($request, $delegate);
    }
}
