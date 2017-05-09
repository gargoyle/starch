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

    /**
     * @var \SplStack
     */
    private $stack;

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
        $start = $this->stack->top();

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

    public function add(callable $middleware)
    {
        if (null === $this->stack) {
            $this->stack = new \SplStack();
            $this->stack->push($this);
        }

        $next = $this->stack->top();
        
        $this->stack->push(function($request, $response) use ($middleware, $next) {
            return call_user_func($middleware, $request, $response, $next);
        });
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $callback = $this->container->get(Router::class)->dispatch($request);

        return $callback($request, $response);
    }
}
