<?php

namespace Starch\Tests\Unit\Router;

use PHPUnit\Framework\TestCase;
use Starch\Router\Route;
use Starch\Tests\FooRequestHandler;

class RouteTest extends TestCase
{
    public function testItUpperCasesMethods()
    {
        $route = new Route(['get', 'post'], '/', new FooRequestHandler());

        $this->assertEquals(['GET', 'POST'], $route->getMethods());
    }
}
