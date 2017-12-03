<?php

namespace Starch\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Starch\App;
use Starch\Router\Route;
use Starch\Router\Router;
use Starch\Tests\TestContainer;

class AppTest extends TestCase
{
    /**
     * @var App
     */
    private $app;

    public function setUp()
    {
        $this->app = new App(new TestContainer());
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
