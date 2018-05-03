<?php

namespace Starch\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Starch\Application;
use Starch\Router\Route;
use Starch\Router\Router;
use Starch\Tests\TestContainer;

class ApplicationTest extends TestCase
{
    /**
     * @var Application
     */
    private $app;

    public function setUp()
    {
        $this->app = new Application(new TestContainer());
    }

    public function testAddGETRoute()
    {
        $this->app->get('/', 'handler');

        $this->assertHasRoute('GET');
    }

    public function testAddPOSTRoute()
    {
        $this->app->post('/', 'handler');

        $this->assertHasRoute('POST');
    }

    public function testAddPUTRoute()
    {
        $this->app->put('/', 'handler');

        $this->assertHasRoute('PUT');
    }

    public function testAddPATCHRoute()
    {
        $this->app->patch('/', 'handler');

        $this->assertHasRoute('PATCH');
    }

    public function testAddDELETERoute()
    {
        $this->app->delete('/', 'handler');

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
