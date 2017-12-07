<?php

namespace Starch\Tests\Functional;

use Interop\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Starch\Tests\AppTestCase;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\TextResponse;

class AppTest extends AppTestCase
{
    public function testProcessesRequest()
    {
        $this->app->get('/', new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface {
                return new TextResponse('foo');
            }
        });

        $response = $this->get('/');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('foo', (string)$response->getBody());
    }

    public function testReturns404ResponseOnNotFound()
    {
        $response = $this->get('/');

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testReturns405ResponseOnMethodNotAllowed()
    {
        $this->app->get('/', new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface {
                return new Response();
            }
        });

        $response = $this->post('/');

        $this->assertEquals(405, $response->getStatusCode());
    }
}
