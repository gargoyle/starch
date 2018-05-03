<?php

namespace Starch\Tests\Functional;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Starch\Exception\MethodNotAllowedException;
use Starch\Exception\NotFoundHttpException;
use Starch\Tests\ApplicationTestCase;
use Zend\Diactoros\Response\TextResponse;

class ApplicationTest extends ApplicationTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->app->get('/', new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface {
                return new TextResponse('foo');
            }
        });

        $this->app->get('/foo', 'handler');

        $this->app->add(new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                $response = $handler->handle($request);

                return $response->withHeader('x-foo', 'bar');
            }
        });
    }

    public function testProcessesRequest()
    {
        $response = $this->get('/');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('foo', (string)$response->getBody());
    }

    public function testCanHaveRequestHandlerAsString()
    {
        $response = $this->get('/foo');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('foo', (string)$response->getBody());
    }

    public function testThrowsNotFoundException()
    {
        $this->expectException(NotFoundHttpException::class);
        $this->get('/bar');
    }

    public function testThrowsMethodNotAllowedException()
    {
        $this->expectException(MethodNotAllowedException::class);
        $this->post('/');
    }
}
