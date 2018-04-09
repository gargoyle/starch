<?php

namespace Starch\Tests\Functional;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Starch\Tests\ApplicationTestCase;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\TextResponse;

class ApplicationTest extends ApplicationTestCase
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
