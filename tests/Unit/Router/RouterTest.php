<?php

namespace Starch\Tests\Unit\Router;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Starch\Request\MethodNotAllowedRequestHandler;
use Starch\Request\NotFoundRequestHandler;
use Starch\Router\Route;
use Starch\Router\Router;
use Zend\Diactoros\ServerRequest;

class RouterTest extends TestCase
{
    public function testSetsNotFoundHandler()
    {
        $router = new Router();
        $router->map(['GET'], '/', 'foo');

        $request = $this->getRequest('GET', '/foo');

        $request = $router->dispatch($request);

        $this->assertInstanceOf(NotFoundRequestHandler::class, $request->getAttribute('requestHandler'));
    }

    public function testSetsMethodNotAllowedHandler()
    {
        $router = new Router();
        $router->map(['GET'], '/', 'foo');

        $request = $this->getRequest('POST', '/');

        $request = $router->dispatch($request);

        $this->assertInstanceOf(MethodNotAllowedRequestHandler::class, $request->getAttribute('requestHandler'));
    }

    public function testAddsRouteToRequest()
    {
        $router = new Router();
        $router->map(['GET'], '/', 'foo');

        $request = $this->getRequest('GET', '/');

        $request = $router->dispatch($request);

        $route = $request->getAttribute('route');

        $this->assertInstanceOf(Route::class, $route);
        $this->assertEquals('foo', $route->getHandler());
        $this->assertEquals(['GET'], $route->getMethods());
        $this->assertEquals('/', $route->getPath());
    }

    public function testAddsArguments()
    {
        $router = new Router();
        $router->map(['GET'], '/{foo}', 'foo');

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
