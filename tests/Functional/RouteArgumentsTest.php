<?php

namespace Starch\Tests\Functional;

use Interop\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Starch\Tests\ApplicationTestCase;
use Zend\Diactoros\Response\TextResponse;

class RouteArgumentsTest extends ApplicationTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testSetsRequest()
    {
        $this->app->get('/', new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface {
                return new TextResponse((string)$request->getUri());
            }
        });

        $this->assertValidRequest('/', 'http://localhost/');
    }

    public function testSetsRouteArguments()
    {
        $this->app->get('/{name}', new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface {
                return new TextResponse($request->getUri()->getHost() . '-' . $request->getAttribute('name'));
            }
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
