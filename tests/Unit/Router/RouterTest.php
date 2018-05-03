<?php

namespace Starch\Tests\Unit\Router;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Starch\Exception\MethodNotAllowedException;
use Starch\Exception\NotFoundHttpException;
use Starch\Router\Route;
use Starch\Router\Router;
use Starch\Tests\FooRequestHandler;
use Zend\Diactoros\ServerRequest;

class RouterTest extends TestCase
{
    /**
     * @var RequestHandlerInterface
     */
    private $requestHandler;

    public function setUp()
    {
        $this->requestHandler = new FooRequestHandler();
    }

    public function testSetsNotFoundHandler()
    {
        $router = new Router();
        $router->map(['GET'], '/', $this->requestHandler);

        $request = $this->getRequest('GET', '/foo');

        $this->expectException(NotFoundHttpException::class);
        $router->dispatch($request);
    }

    public function testSetsMethodNotAllowedHandler()
    {
        $router = new Router();
        $router->map(['GET'], '/', $this->requestHandler);

        $request = $this->getRequest('POST', '/');

        $this->expectException(MethodNotAllowedException::class);
        $router->dispatch($request);
    }

    public function testAddsRouteToRequest()
    {
        $router = new Router();
        $router->map(['GET'], '/', $this->requestHandler);

        $request = $this->getRequest('GET', '/');

        $request = $router->dispatch($request);

        /** @var Route $route */
        $route = $request->getAttribute('route');

        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals('foo', (string) $route->getHandler()->handle($request)->getBody());
        $this->assertEquals(['GET'], $route->getMethods());
        $this->assertEquals('/', $route->getPath());
    }

    public function testAddsArguments()
    {
        $router = new Router();
        $router->map(['GET'], '/{foo}', $this->requestHandler);

        $request = $this->getRequest('GET', '/bar');

        $request = $router->dispatch($request);

        $this->assertEquals('bar', $request->getAttribute('foo'));
    }

    /**
     * @param string $method
     * @param string $path
     *
     * @return ServerRequestInterface
     */
    private function getRequest(string $method, string $path)
    {
        return new ServerRequest([], [], $path, $method);
    }
}
