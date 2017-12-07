<?php

namespace Starch\Tests\Integration;

use Interop\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Starch\Application;
use Starch\Router\RouterMiddleware;
use Starch\Tests\TestContainer;
use Zend\Diactoros\Response\TextResponse;

class IntegrationApp extends Application
{
    public function __construct()
    {
        parent::__construct(new TestContainer());

        $this->get('/', function() {
            return new TextResponse('Hello, world!');
        });

        $this->add(function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
            $response = $handler->handle($request);

            return $response->withHeader('x-foo', 'bar');
        });
        $this->add(RouterMiddleware::class);
    }
}
