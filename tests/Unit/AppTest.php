<?php

namespace Starch\Tests\Unit;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Starch\App;
use PHPUnit\Framework\TestCase;
use Starch\Middleware\StackInterface;
use Starch\Router\Route;
use Starch\Router\Router;

class AppTest extends TestCase
{
    /**
     * @var App
     */
    private $app;

    public function setUp()
    {
        $this->app = new App();
    }

    public function testAllowsStringAsMiddleware()
    {
        $this->app->add(StubMiddleware::class);

        $this->assertStackHasMiddleware();
    }

    public function testAllowInstanceAsMiddleware()
    {
        $this->app->add(new StubMiddleware());

        $this->assertStackHasMiddleware();
    }

    public function testAllowAnonymousClassAsMiddleware()
    {
        $this->app->add(new class implements MiddlewareInterface
        {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                return $handler->handle($request);
            }
        });

        $this->assertStackHasMiddleware();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testStringMustBeMiddlewareInterface()
    {
        $this->app->add(Stub::class);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInstanceMustBeMiddlewareInterface()
    {
        $this->app->add(new Stub());
    }

    private function assertStackHasMiddleware()
    {
        $stack = $this->app->getContainer()->get(StackInterface::class);

        $refl = new \ReflectionClass($stack);
        $items = $refl->getProperty('items');
        $items->setAccessible(true);

        $this->assertCount(1,$items->getValue($stack));
    }

    public function testAddGETRoute()
    {
        $this->app->get('/', 'foo');

        $this->assertHasRoute('GET');
    }

    public function testAddPOSTRoute()
    {
        $this->app->post('/', 'foo');

        $this->assertHasRoute('POST');
    }

    public function testAddPUTRoute()
    {
        $this->app->put('/', 'foo');

        $this->assertHasRoute('PUT');
    }

    public function testAddPATCHRoute()
    {
        $this->app->patch('/', 'foo');

        $this->assertHasRoute('PATCH');
    }

    public function testAddDELETERoute()
    {
        $this->app->delete('/', 'foo');

        $this->assertHasRoute('DELETE');
    }

    private function assertHasRoute($method)
    {
        $router = $this->app->getContainer()->get(Router::class);

        $reflected = new \ReflectionClass($router);
        $items = $reflected->getProperty('routes');
        $items->setAccessible(true);

        /** @var Route[] $routes */
        $routes = $items->getValue($router);

        $this->assertCount(1,$routes);
        $this->assertEquals('/', $routes[0]->getPath());
        $this->assertEquals([$method], $routes[0]->getMethods());
    }
}

class Stub {
}

class StubMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request);
    }
}