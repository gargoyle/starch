<?php

namespace Starch;

use DI\ContainerBuilder;
use function DI\object;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
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
    }

    public function run(RequestInterface $request = null)
    {
        if (null === $request) {
            $request = $request = ServerRequestFactory::fromGlobals();
        }

        $callback = $this->dispatch($request);

        $response = $callback(new Response());

        $this->container->get(SapiEmitter::class)->emit($response);

    }

    public function dispatch(RequestInterface $request)
    {
        $routeInfo = $this->container->get(Dispatcher::class)->dispatch($request->getMethod(), $request->getUri()->getPath());

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                // ... 404 Not Found
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                // ... 405 Method Not Allowed
                break;
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                return $handler;
                break;
        }
    }

    private function buildContainer()
    {
        $builder = new ContainerBuilder();

        $builder->addDefinitions([
            Dispatcher::class => function() {
                return simpleDispatcher(function(RouteCollector $r) {
                    $r->addRoute('GET', '/', function(ResponseInterface $response) {
                        $response->getBody()->write('foo');

                        return $response;
                    });
                });
            },

            EmitterInterface::class => object(SapiEmitter::class),
        ]);

        $this->container = $builder->build();
    }
}
