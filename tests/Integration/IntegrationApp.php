<?php

namespace Starch\Tests\Integration;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Starch\Application;
use Starch\Tests\TestContainer;
use Zend\Diactoros\Response\TextResponse;

class IntegrationApp extends Application
{
    public function __construct()
    {
        parent::__construct(new TestContainer());

        $this->get('/', new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new TextResponse('Hello, world!');
            }
        });

        $this->get('/foo', 'handler');

        $this->add(new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                try {
                    $response = $handler->handle($request);
                } catch (Exception $e) {
                    return new TextResponse($e->getMessage(), $e->getCode());
                }

                return $response->withHeader('x-foo', 'bar');
            }
        });
    }
}
