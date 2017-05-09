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
        $this->container->get(Router::class)->get('/', function($request, ResponseInterface $response) {
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

        $stack = new \SplStack();

        $next = $callback;

        $middleware = function($request, ResponseInterface $response, callable $next) {
            $response->getBody()->write(' Before1 ');
            $response = $next($request, $response);
            $response->getBody()->write(' After1 ');

            return $response;
        };


        $stack[] = function($request, $response) use ($middleware, $next) {
            return call_user_func($middleware, $request, $response, $next);
        };

        $stack[] = $callback;

        $start = $stack->bottom();

        return $start($request, new Response());
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
