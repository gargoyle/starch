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

    public function loadRoutes() : void
    {
        $this->get('/', function($request, ResponseInterface $response) {
            $response->getBody()->write('Hello, world!');

            return $response;
        });
    }

    #### ROUTER HELPER METHODS ####

    public function get(string $route, callable $handler) : void
    {
        $this->container->get(Router::class)->map(['GET'], $route, $handler);
    }

    /**
     * Builds a request from globals, processes it and emits the response.
     */
    public function run() : void
    {
        $request = $request = ServerRequestFactory::fromGlobals();

        $response = $this->process($request);

        $this->container->get(EmitterInterface::class)->emit($response);
    }

    public function process(RequestInterface $request) : ResponseInterface
    {
        $start = $this->stack->top();

        return $start($request, new Response());
    }

    private function buildContainer() : void
    {
        $builder = new ContainerBuilder();

        $builder->addDefinitions([
            Router::class => object(),

            EmitterInterface::class => object(SapiEmitter::class),
        ]);

        $this->configureContainer($builder);

        $this->container = $builder->build();
    }

    public function add(callable $middleware) : void
    {
        if (null === $this->stack) {
            $this->initiateStack();
        }

        $next = $this->stack->top();
        
        $this->stack->push(function($request, $response) use ($middleware, $next) {
            return call_user_func($middleware, $request, $response, $next);
        });
    }

    private function initiateStack() : void
    {
        $this->stack = new \SplStack();
        $this->stack->push(function($request, $response) : ResponseInterface {
            $callback = $this->container->get(Router::class)->dispatch($request);

            return $callback($request, $response);
        });
    }

    /**
     * Override this method to add extra definitions to your app
     */
    public function configureContainer(ContainerBuilder $builder) : void
    {
    }
}
