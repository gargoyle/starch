<?php

namespace Starch;

use DI\ContainerBuilder;
use function DI\object;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Starch\Router\Router;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\EmitterInterface;
use Zend\Diactoros\Response\SapiEmitter;
use Zend\Diactoros\ServerRequestFactory;

class App
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct()
    {
        $this->buildContainer();
        $this->loadRoutes();
    }

    public function loadRoutes()
    {
        $this->container->get(Router::class)->get('/', function(ResponseInterface $response) {
            $response->getBody()->write('Hello, world!');

            return $response;
        });
    }

    public function run()
    {
        $request = $request = ServerRequestFactory::fromGlobals();

        $response = $this->process($request);

        $this->container->get(SapiEmitter::class)->emit($response);
    }

    public function process(RequestInterface $request) : ResponseInterface
    {
        $callback = $this->container->get(Router::class)->dispatch($request);

        return $callback(new Response());
    }

    private function buildContainer()
    {
        $builder = new ContainerBuilder();

        $builder->addDefinitions([
            Router::class => object(),

            EmitterInterface::class => object(SapiEmitter::class),
        ]);

        $this->container = $builder->build();
    }
}
