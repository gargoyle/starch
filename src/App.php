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
    }

    /**
     * Override this method to add extra definitions to your app
     *
     * @return void
     */
    public function configureContainer(ContainerBuilder $builder) : void
    {
    }

    /**
     * Add middleware
     *
     * This method will add the given callable to the middleware stack
     *
     * @param  callable $middleware
     *
     * @return void
     */
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

    /********************************************************************************
     * Router proxy methods
     *******************************************************************************/

    /**
     * Add a GET route
     *
     * @param  string   $route
     * @param  callable $handler
     *
     * @return void
     */
    public function get(string $route, callable $handler) : void
    {
        $this->container->get(Router::class)->map(['GET'], $route, $handler);
    }

    /********************************************************************************
     * Running the actual app
     *******************************************************************************/

    /**
     * Run the app
     *
     * Will build a request from PHP globals, process that request and then emit it
     *
     * @return void
     */
    public function run() : void
    {
        $request = $request = ServerRequestFactory::fromGlobals();

        $response = $this->process($request);

        $this->container->get(EmitterInterface::class)->emit($response);
    }

    /**
     * Process request
     *
     * Will send the request through the middleware stack
     *
     * @param  RequestInterface $request
     *
     * @return ResponseInterface
     */
    public function process(RequestInterface $request) : ResponseInterface
    {
        $start = $this->stack->top();

        return $start($request, new Response());
    }

    /********************************************************************************
     * Private methods
     *******************************************************************************/

    /**
     * Build the container with a couple base service
     *
     * These base services can be overridden in self::configureContainer
     *
     * @return void
     */
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

    /**
     * Initiate the stack, adding the called route handler first
     *
     * @return void
     */
    private function initiateStack() : void
    {
        $this->stack = new \SplStack();
        $this->stack->push(function($request, $response) : ResponseInterface {
            $callback = $this->container->get(Router::class)->dispatch($request);

            return $callback($request, $response);
        });
    }
}
