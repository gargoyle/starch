<?php

namespace Starch\Tests\Functional;

use Psr\Http\Message\ServerRequestInterface;
use Starch\Router\RouterMiddleware;
use Starch\Tests\AppTestCase;
use Zend\Diactoros\Response;

class RouteArgumentsTest extends AppTestCase
{
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
}
