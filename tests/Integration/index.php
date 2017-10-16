<?php

namespace Starch\Tests\Integration;

require_once('../../vendor/autoload.php');

use Starch\App;
use Starch\Router\RouterMiddleware;
use Zend\Diactoros\Response;

class IntegrationTest extends App
{
    public function __construct()
    {
        parent::__construct();

        $this->get('/', function() {
            $response = new Response();
            $response->getBody()->write('Hello, world!');

            return $response;
        });

        $this->add(RouterMiddleware::class);
    }
}

$app = new IntegrationTest();
$app->run();
