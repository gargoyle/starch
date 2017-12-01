<?php

namespace Starch\Tests\Unit\Middleware;

use Interop\Http\Server\RequestHandlerInterface;
use Invoker\InvokerInterface;
use PHPUnit_Framework_MockObject_MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Starch\Middleware\Delegate;
use PHPUnit\Framework\TestCase;
use Starch\Middleware\StackItem;
use Starch\Router\Route;

class DelegateTest extends TestCase
{
    /**
     * @var StackItem|PHPUnit_Framework_MockObject_MockObject
     */
    private $item;

    /**
     * @var RequestHandlerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $next;

    /**
     * @var InvokerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $invoker;

    /**
     * @var ServerRequestInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    public function setUp()
    {
        $this->item = $this->createMock(StackItem::class);
        $this->next = $this->createMock(RequestHandlerInterface::class);
        $this->invoker = $this->createMock(InvokerInterface::class);
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->request->method('getAttribute')
            ->willReturn($this->createMock(Route::class));
    }

    public function testSkipsToNextIfItemSaysSo()
    {
        $this->item->expects($this->once())
            ->method('executeFor')
            ->willReturn(false);

        $this->next->expects($this->once())
            ->method('handle')
            ->willReturn($this->createMock(ResponseInterface::class));

        $delegate = new Delegate($this->item, $this->next, $this->invoker);

        $delegate->handle($this->request);
    }

    public function testCallsMiddlewareIfItemSaysSo()
    {
        $this->item->expects($this->once())
            ->method('executeFor')
            ->willReturn(true);

        $this->next->expects($this->never())
            ->method('handle');

        $this->invoker->expects($this->once())
            ->method('call')
            ->willReturn($this->createMock(ResponseInterface::class));

        $delegate = new Delegate($this->item, $this->next, $this->invoker);

        $delegate->handle($this->request);
    }
}
