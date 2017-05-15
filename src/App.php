<?php

namespace Starch;

use DI\Container;
use DI\ContainerBuilder;
use function DI\object;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Starch\Exception\ExceptionHandler;
use Starch\Middleware\Stack;
use Starch\Middleware\StackInterface;
use Starch\Router\Router;
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

    /**
     * Override this method to add extra definitions to your app
     *
     * @return void
     */
    public function configureContainer(ContainerBuilder $builder) : void
    {
    }

    /**
     * TODO: Add param validation here and potentially the string-> instanceof MiddlewareInterface check so we can take it out of Stack?
     * Add middleware to the stack
     *
     * @param mixed $middleware
     *
     * @return void
     */
    public function add($middleware) : void
    {
        $this->container->get(StackInterface::class)->add($middleware);
    }

    /********************************************************************************
     * Router proxy methods
     *******************************************************************************/

    /**
     * Add a GET route
     *
     * @param  string $path
     * @param  mixed  $handler
     *
     * @return void
     */
    public function get(string $path, $handler) : void
    {
        $this->map(['GET'], $path, $handler);
    }

    /**
     * Add a POST route
     *
     * @param  string $path
     * @param  mixed  $handler
     *
     * @return void
     */
    public function post(string $path, $handler) : void
    {
        $this->map(['POST'], $path, $handler);
    }

    /**
     * Add a PUT route
     *
     * @param  string $path
     * @param  mixed  $handler
     *
     * @return void
     */
    public function put(string $path, $handler) : void
    {
        $this->map(['PUT'], $path, $handler);
    }

    /**
     * Add a PATCH route
     *
     * @param  string $path
     * @param  mixed  $handler
     *
     * @return void
     */
    public function patch(string $path, $handler) : void
    {
        $this->map(['PATCH'], $path, $handler);
    }

    /**
     * Add a DELETE route
     *
     * @param  string $path
     * @param  mixed  $handler
     *
     * @return void
     */
    public function delete(string $path, $handler) : void
    {
        $this->map(['DELETE'], $path, $handler);
    }

    /**
     * Map multiple methods for a route
     *
     * @param  array  $methods
     * @param  string $path
     * @param  mixed  $handler
     *
     * @return void
     */
    public function map(array $methods, string $path, $handler) : void
    {
        $this->container->get(Router::class)->map($methods, $path, $handler);
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
     * Dispatch the request to the router
     * Add the returned handler as the last Middleware
     * Send the Request through the stack
     *
     * @param  ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request) : ResponseInterface
    {
        try {
            $request = $this->container->get(Router::class)->dispatch($request);

            return $this->container->get(StackInterface::class)->resolve($request);
        } catch (\Exception $e) {
            return $this->container->get(ExceptionHandler::class)->handle($e);
        }
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
            EmitterInterface::class => object(SapiEmitter::class),

            StackInterface::class => function(Container $container) {
                return new Stack($container);
            },
        ]);

        $this->configureContainer($builder);

        $this->container = $builder->build();
    }
}
