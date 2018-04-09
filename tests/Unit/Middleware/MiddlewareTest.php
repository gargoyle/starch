<?php

namespace Starch\Tests\Unit\Middleware;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Psr\Http\Server\MiddlewareInterface;
use Starch\Middleware\Middleware;
use Starch\Router\Route;

class MiddlewareTest extends TestCase
{
    /**
     * @var MiddlewareInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $middleware;
    /**
     * @var Route|PHPUnit_Framework_MockObject_MockObject
     */
    private $route;

    public function setUp()
    {
        $this->middleware = $this->createMock(MiddlewareInterface::class);
        $this->route = $this->createMock(Route::class);
    }

    public function testExecutesWithoutContstraint()
    {
        $this->route->expects($this->never())
            ->method('getPath');
        $item = new Middleware($this->middleware);
        $this->assertTrue($item->executeFor($this->route));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testExecutesWithConstraint(string $path, string $constraint, bool $result)
    {
        $this->route->expects($this->once())
            ->method('getPath')
            ->willReturn($path);

        $item = new Middleware($this->middleware, $constraint);
        $this->assertEquals($result, $item->executeFor($this->route));
    }

    public function dataProvider()
    {
        return [
            ['/', '/', true],
            ['/', '/foo', false],
            ['/foo', '/', true],
            ['/foo/bar', '/foo', true],
            ['/foo', 'foo', false],
        ];
    }
}
