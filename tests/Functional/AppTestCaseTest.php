<?php

namespace Starch\Tests\Functional;

use Psr\Http\Message\ServerRequestInterface;
use Starch\Router\RouterMiddleware;
use Starch\Tests\AppTestCase;
use Zend\Diactoros\Response\TextResponse;

class AppTestCaseTest extends AppTestCase
{
    public function testTestCaseCanSendPost()
    {
        $this->app->post('/', function(ServerRequestInterface $request) {
            $response = new TextResponse($request->getParsedBody()['name']);

            return $response;
        });
        $this->app->add(RouterMiddleware::class);

        $response = $this->post('/', ['name' => 'foo']);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('foo', (string)$response->getBody());
    }
}
