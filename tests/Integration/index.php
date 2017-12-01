<?php

namespace Starch\Tests\Integration;

require_once('../../vendor/autoload.php');

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Starch\App;
use Starch\Router\RouterMiddleware;
use Zend\Diactoros\Response\TextResponse;

class IntegrationTest extends App
{
    public function __construct()
    {
        parent::__construct();

        $this->get('/', function() {
            return new TextResponse('Hello, world!');
        });

        $this->add(function (ServerRequestInterface $request, DelegateInterface $delegate) {
            $response = $delegate->process($request);

            return $response->withHeader('x-foo', 'bar');
        });
        $this->add(RouterMiddleware::class);
    }
}

$app = new IntegrationTest();
$app->run();
