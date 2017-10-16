<?php

namespace Starch\Tests\Functional;

use Psr\Http\Message\ServerRequestInterface;
use Starch\Router\RouterMiddleware;
use Starch\Tests\AppTestCase;
use Zend\Diactoros\Response\TextResponse;

class RouteArgumentsTest extends AppTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->app->add(RouterMiddleware::class);
    }

    public function testSetsRequest()
    {
        $this->app->get('/', function(ServerRequestInterface $request) {
            return new TextResponse((string)$request->getUri());
        });

        $this->assertValidRequest('/', 'http://localhost/');
    }

    public function testSetsRouteArguments()
    {
        $this->app->get('/{name}', function(ServerRequestInterface $request, $name) {
            return new TextResponse($request->getUri()->getHost() . '-' . $name);
        });

        $this->assertValidRequest('/foo', 'localhost-foo');
    }

    private function assertValidRequest($uri, $expectedResponse)
    {
        $response = $this->get($uri);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($expectedResponse, (string)$response->getBody());
    }
}
