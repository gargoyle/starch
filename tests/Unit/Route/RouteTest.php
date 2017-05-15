<?php

namespace Starch\Tests\Unit\Route;

use Starch\Router\Route;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    public function testItUpperCasesMethods()
    {
        $route = new Route(['get', 'post'], '/', 'foo');

        $this->assertEquals(['GET', 'POST'], $route->getMethods());
    }
}
