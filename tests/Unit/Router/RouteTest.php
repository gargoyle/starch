<?php

namespace Starch\Tests\Unit\Router;

use PHPUnit\Framework\TestCase;
use Starch\Router\Route;

class RouteTest extends TestCase
{
    public function testItUpperCasesMethods()
    {
        $route = new Route(['get', 'post'], '/', 'foo');

        $this->assertEquals(['GET', 'POST'], $route->getMethods());
    }
}
