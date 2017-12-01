<?php

namespace Starch\Tests\Unit\Middleware;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Invoker\Invoker;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Starch\Middleware\Stack;
use PHPUnit\Framework\TestCase;
use Starch\Middleware\StackItem;
use Starch\Router\Route;
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
        $request->method('getAttribute')
            ->willReturn($this->createMock(Route::class));

        $response = $stack->resolve($request);

        $this->assertEquals('foobarbaz', (string) $response->getBody());
    }

    public function testSkipsConstrainedMiddlewares()
    {
        $stack = new Stack(new Invoker());

        $stack->add(new StackItem(new BazMiddleware(), '/baz'));
        $stack->add(new StackItem(new BarMiddleware()));
        $stack->add(new StackItem(new FooMiddleware()));

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')
            ->willReturn($this->createMock(Route::class));

        $response = $stack->resolve($request);

        $this->assertEquals('foobar', (string) $response->getBody());
    }

    public function testExecutesConstrainedMiddlewares()
    {
        $stack = new Stack(new Invoker());

        $stack->add(new StackItem(new BazMiddleware(), '/baz'));
        $stack->add(new StackItem(new BarMiddleware()));
        $stack->add(new StackItem(new FooMiddleware()));

        $route = $this->createMock(Route::class);
        $route->method('getPath')
            ->willReturn('/baz/foo');

        $request = $this->createMock(ServerRequestInterface::class);

        $request->method('getAttribute')
            ->willReturn($route);

        $response = $stack->resolve($request);

        $this->assertEquals('foobarbaz', (string) $response->getBody());
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
        $request->method('getAttribute')
            ->willReturn($this->createMock(Route::class));

        $stack->resolve($request);
    }
}

class FooMiddleware implements MiddlewareInterface
{
    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
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
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $response->getBody()->write('bar');

        return $response;
    }
}

class BazMiddleware implements MiddlewareInterface
{
    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $response->getBody()->write('baz');

        return $response;
    }
}