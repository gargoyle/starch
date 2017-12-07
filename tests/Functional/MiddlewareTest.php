<?php

namespace Starch\Tests\Functional;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Starch\Tests\ApplicationTestCase;
use Zend\Diactoros\Response;

class MiddlewareTest extends ApplicationTestCase
{
    public function testAcceptsMiddleware()
    {
        $this->app->get('/', new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface {
                $response = new Response();
                $response->getBody()->write($request->getHeader('x-name')[0]);

                return $response;
            }
        });

        $this->app->add(new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                $request = $request->withHeader('x-name', 'foo');

                return $handler->handle($request);
            }
        });
        $this->app->add(new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                $response = $handler->handle($request);
                $response->getBody()->write('bar');

                return $response;
            }
        });

        $response = $this->get('/');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('foobar', (string)$response->getBody());
    }

    public function testConstrainsMiddlewareToPath()
    {
        $this->app->get('/foo', new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface {
                return new Response();
            }
        });

        $this->app->add(new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                $response = $handler->handle($request);

                return $response->withHeader('x-foo', 'foo');
            }
        }, '/foo');

        $this->app->add(new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                $response = $handler->handle($request);

                return $response->withHeader('x-bar', 'foo');
            }
        }, '/bar');

        $response = $this->get('/foo');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('x-foo'));
        $this->assertFalse($response->hasHeader('x-bar'));
    }
}
