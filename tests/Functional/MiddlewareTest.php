<?php

namespace Starch\Tests\Functional;

use Interop\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Starch\Router\RouterMiddleware;
use Starch\Tests\AppTestCase;
use Zend\Diactoros\Response;

class MiddlewareTest extends AppTestCase
{
    public function testAcceptsMiddleware()
    {
        $this->app->get('/', function(ServerRequestInterface $request) {
            $response = new Response();
            $response->getBody()->write($request->getHeader('x-name')[0]);

            return $response;
        });

        $this->app->add(function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
            $request = $request->withHeader('x-name', 'foo');

            return $handler->handle($request);
        });
        $this->app->add(function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
            $response = $handler->handle($request);
            $response->getBody()->write('bar');

            return $response;
        });
        $this->app->add(RouterMiddleware::class);

        $response = $this->get('/');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('foobar', (string)$response->getBody());
    }

    public function testConstrainsMiddlewareToPath()
    {
        $this->app->get('/foo', function() {
            return new Response();
        });

        $this->app->add(function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
            $response =  $handler->handle($request);

            return $response->withHeader('x-foo', 'foo');
        }, '/foo');

        $this->app->add(function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
            $response =  $handler->handle($request);

            return $response->withHeader('x-bar', 'foo');
        }, '/bar');
        $this->app->add(RouterMiddleware::class);

        $response = $this->get('/foo');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('x-foo'));
        $this->assertFalse($response->hasHeader('x-bar'));
    }
}
