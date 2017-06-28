<?php

namespace Starch\Tests\Functional;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Starch\Router\RouterMiddleware;
use Starch\Tests\AppTestCase;
use Zend\Diactoros\Response;

class AppTest extends AppTestCase
{
    public function testReturns404ResponseOnNotFound()
    {
        $response = $this->get('/');

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testReturns404ResponseOnMethodNotAllowed()
    {
        $this->app->get('/', function() {
            return new Response();
        });

        $response = $this->post('/');

        $this->assertEquals(405, $response->getStatusCode());
    }

    public function testAcceptsMiddleware()
    {
        $this->app->get('/', function(ServerRequestInterface $request) {
            $response = new Response();
            $response->getBody()->write($request->getHeader('x-name')[0]);

            return $response;
        });

        $this->app->add(function (ServerRequestInterface $request, DelegateInterface $delegate) {
            $request = $request->withHeader('x-name', 'foo');

            return $delegate->process($request);
        });
        $this->app->add(function (ServerRequestInterface $request, DelegateInterface $delegate) {
            $response = $delegate->process($request);
            $response->getBody()->write('bar');

            return $response;
        });
        $this->app->add(RouterMiddleware::class);

        $response = $this->get('/');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('foobar', (string)$response->getBody());
    }

    public function testSetsRouteArguments()
    {
        $this->app->get('/{name}', function(ServerRequestInterface $request, $name) {
            $response = new Response();
            $response->getBody()->write($name);

            return $response;
        });
        $this->app->add(RouterMiddleware::class);

        $response = $this->get('/foo');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('foo', (string)$response->getBody());
    }

    public function testTestCaseCanSendPost()
    {
        $this->app->post('/', function(ServerRequestInterface $request) {
            $response = new Response();
            $response->getBody()->write($request->getParsedBody()['name']);

            return $response;
        });
        $this->app->add(RouterMiddleware::class);

        $response = $this->post('/', ['name' => 'foo']);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('foo', (string)$response->getBody());
    }
}
