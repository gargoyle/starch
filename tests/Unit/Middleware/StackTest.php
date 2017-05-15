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
use Starch\Router\Route;
use Zend\Diactoros\Response;

class StackTest extends TestCase
{
    public function testResolvesStack()
    {
        $stack = new Stack(new Invoker());

        $stack->add(new StackItem(new BazMiddleware()));
        $stack->add(new StackItem(new BarMiddleware()));

        $route = $this->createMock(Route::class);
        $route->expects($this->once())
              ->method('getArguments')
              ->willReturn([
                  'foo' => 'foo'
              ]);
        $route->expects($this->once())
              ->method('getHandler')
              ->willReturn(function($request, $foo) {
                  $response = new Response();
                  $response->getBody()->write($foo);

                  return $response;
              });

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getAttribute')
                ->with($this->equalTo('route'))
                ->willReturn($route);

        $response = $stack->resolve($request);

        $this->assertEquals('foobarbaz', (string) $response->getBody());
    }

    public function testSkipsConstrainedMiddlewares()
    {
        $stack = new Stack(new Invoker());

        $stack->add(new StackItem(new BazMiddleware(), '/baz'));
        $stack->add(new StackItem(new BarMiddleware()));

        $route = $this->createMock(Route::class);
        $route->expects($this->once())
              ->method('getArguments')
              ->willReturn([
                  'foo' => 'foo'
              ]);
        $route->expects($this->once())
              ->method('getHandler')
              ->willReturn(function($request, $foo) {
                  $response = new Response();
                  $response->getBody()->write($foo);

                  return $response;
              });

        $uri = $this->createMock(UriInterface::class);
        $uri->expects($this->once())
            ->method('getPath')->willReturn('/');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getAttribute')
                ->with($this->equalTo('route'))
                ->willReturn($route);

        $request->expects($this->once())
            ->method('getUri')
            ->willReturn($uri);

        $response = $stack->resolve($request);

        $this->assertEquals('foobar', (string) $response->getBody());
    }

    public function testWorksWithEmptyStack()
    {
        $stack = new Stack(new Invoker());

        $route = $this->createMock(Route::class);
        $route->expects($this->once())
              ->method('getArguments')
              ->willReturn([
                  'foo' => 'foo'
              ]);
        $route->expects($this->once())
              ->method('getHandler')
              ->willReturn(function($request, $foo) {
                  $response = new Response();
                  $response->getBody()->write($foo);

                  return $response;
              });

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getAttribute')
                ->with($this->equalTo('route'))
                ->willReturn($route);

        $response = $stack->resolve($request);

        $this->assertEquals('foo', (string) $response->getBody());
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