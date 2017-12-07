<?php

namespace Starch\Tests\Functional;

use Interop\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Starch\Tests\ApplicationTestCase;
use Zend\Diactoros\Response;

class ApplicationTestCaseTest extends ApplicationTestCase
{
    public function testTestCaseCanSendPost()
    {
        $this->app->post('/', new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface {
                $response = new Response();
                $response->getBody()->write($request->getParsedBody()['name']);

                return $response;
            }
        });

        $response = $this->post('/', ['name' => 'foo']);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('foo', (string)$response->getBody());
    }
}
