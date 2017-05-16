<?php

namespace Starch\Tests\Unit\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Invoker\Invoker;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Starch\Middleware\Stack;
use PHPUnit\Framework\TestCase;
use Starch\Middleware\StackItem;
use Zend\Diactoros\Response;

class StackTest extends TestCase
{
    public function testResolvesStack()
    {
        $stack = new Stack(new Invoker());

        $stack->add(new StackItem(new BazMiddleware()));
        $stack->add(new StackItem(new BarMiddleware()));
        $stack->add(new StackItem(new FooMiddleware()));

        $request = $this->createMock(ServerRequestInterface::class);

        $response = $stack->resolve($request);

        $this->assertEquals('foobarbaz', (string) $response->getBody());
    }

    public function testSkipsConstrainedMiddlewares()
    {
        $stack = new Stack(new Invoker());

        $stack->add(new StackItem(new BazMiddleware(), '/baz'));
        $stack->add(new StackItem(new BarMiddleware()));
        $stack->add(new StackItem(new FooMiddleware()));

        $uri = $this->createMock(UriInterface::class);
        $uri->expects($this->once())
            ->method('getPath')->willReturn('/');

        $request = $this->createMock(ServerRequestInterface::class);

        $request->expects($this->once())
            ->method('getUri')
            ->willReturn($uri);

        $response = $stack->resolve($request);

        $this->assertEquals('foobar', (string) $response->getBody());
    }

    /**
     * @expectedException \LogicException
     */
    public function testThrowsLogicExceptionOnEmptyStack()
    {
        $stack = new Stack(new Invoker());

        $request = $this->createMock(ServerRequestInterface::class);

        $stack->resolve($request);
    }

    /**
     * @expectedException \LogicException
     */
    public function testThrowsLogicExceptionIfLastMiddlewareCallsDelegate()
    {
        $stack = new Stack(new Invoker());

        $stack->add(new StackItem(new BarMiddleware()));

        $request = $this->createMock(ServerRequestInterface::class);

        $stack->resolve($request);
    }
}

class FooMiddleware implements MiddlewareInterface
{
    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $response = new Response();

        $response->getBody()->write('foo');

        return $response;
    }
}

class BarMiddleware implements MiddlewareInterface
{
    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $response = $delegate->process($request);

        $response->getBody()->write('bar');

        return $response;
    }
}

class BazMiddleware implements MiddlewareInterface
{
    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $response = $delegate->process($request);

        $response->getBody()->write('baz');

        return $response;
    }
}