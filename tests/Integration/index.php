<?php

namespace Starch\Tests\Integration;

require_once('../../vendor/autoload.php');

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Starch\Application;
use Starch\Tests\TestContainer;
use Zend\Diactoros\Response\TextResponse;

class IntegrationTest extends Application
{
    public function __construct()
    {
        parent::__construct(new TestContainer());

        $this->get('/', new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface {
                return new TextResponse('Hello, world!');
            }
        });

        $this->add(new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                $response = $handler->handle($request);

                return $response->withHeader('x-foo', 'bar');
            }
        });
    }
}

$app = new IntegrationTest();
$app->run();
