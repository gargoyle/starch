<?php

namespace Starch\Tests\Functional;

use Starch\Router\RouterMiddleware;
use Starch\Tests\ApplicationTestCase;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\TextResponse;

class ApplicationTest extends ApplicationTestCase
{
    public function testProcessesRequest()
    {
        $this->app->get('/', function() {
            $response = new TextResponse('foo');

            return $response;
        });

        $this->app->add(RouterMiddleware::class);

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
        $this->app->get('/', function() {
            return new Response();
        });

        $response = $this->post('/');

        $this->assertEquals(405, $response->getStatusCode());
    }
}
