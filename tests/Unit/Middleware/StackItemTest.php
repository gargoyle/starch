<?php

namespace Starch\Tests\Unit\Middleware;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use PHPUnit_Framework_MockObject_MockObject;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Starch\Middleware\StackItem;
use PHPUnit\Framework\TestCase;

class StackItemTest extends TestCase
{
    /**
     * @var MiddlewareInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $middleware;

    /**
     * @var ServerRequestInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    public function setUp()
    {
        $this->middleware = $this->createMock(MiddlewareInterface::class);
        $this->request = $this->createMock(ServerRequestInterface::class);
    }

    public function testExecutesWithoutContstraint()
    {
        $this->request->expects($this->never())->method('getUri');

        $item = new StackItem($this->middleware);

        $this->assertTrue($item->executeFor($this->request));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testExecutesWithConstraint(string $path, string $constraint, bool $result)
    {
        $uri = $this->createMock(UriInterface::class);
        $uri->expects($this->once())
            ->method('getPath')->willReturn($path);

        $this->request->expects($this->once())
                      ->method('getUri')->willReturn($uri);

        $item = new StackItem($this->middleware, $constraint);

        $this->assertEquals($result, $item->executeFor($this->request));
    }

    public function dataProvider()
    {
        return [
            ['/', '/', true],
            ['/', '/foo', false],
            ['/foo', '/', false],
            ['/foo', '/.+', true],
            ['/foo/bar', '/foo', false],
            ['/foo/bar', '/foo.+', true],
            ['/foo', 'foo', false],
        ];
    }
}
