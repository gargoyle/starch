<?php

namespace Starch;

use Closure;
use DI\Container;
use DI\ContainerBuilder;
use function DI\autowire;
use function DI\get;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use InvalidArgumentException;
use Invoker\InvokerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Starch\Exception\ExceptionHandler;
use Starch\Middleware\ClosureMiddleware;
use Starch\Middleware\Stack;
use Starch\Middleware\StackInterface;
use Starch\Middleware\StackItem;
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

    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
        if (null === $container) {
            $this->buildContainer();
        }
    }

    /**
     * Override this method to add extra definitions to your app (don't forget to call parent::configureContainer)
     * Or add your own implementations of the definitions below
     * IMPORTANT: The definitions defined here are required for the app to run successfully
     *
     * @return void
     */
    public function configureContainer(ContainerBuilder $builder) : void
    {
        $builder->addDefinitions([
            EmitterInterface::class => autowire(SapiEmitter::class),

            InvokerInterface::class => get(Container::class),

            StackInterface::class => autowire(Stack::class),
        ]);
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer() : ContainerInterface
    {
        return $this->container;
    }

    /**
     * Add middleware to the stack
     *
     * @param Closure|MiddlewareInterface|string $middleware
     * @param string|null $pathConstraint
     *
     * @return void
     */
    public function add($middleware, string $pathConstraint = null) : void
    {
        if ($middleware instanceof Closure) {
            $middleware = new ClosureMiddleware($middleware);
        }

        if (is_string($middleware)) {
            $middleware = $this->getContainer()->get($middleware);
        }

        if (!$middleware instanceof MiddlewareInterface) {
            throw new InvalidArgumentException(sprintf("Middleware must be instance of MiddlewareInterface (given '%s').", get_class($middleware)));
        }

        $this->getContainer()->get(StackInterface::class)->add(new StackItem($middleware, $pathConstraint));
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
     * @param  string[] $methods
     * @param  string   $path
     * @param  mixed    $handler
     *
     * @return void
     */
    public function map(array $methods, string $path, $handler) : void
    {
        $this->getContainer()->get(Router::class)->map($methods, $path, $handler);
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

        $this->getContainer()->get(EmitterInterface::class)->emit($response);
    }

    /**
     * Dispatch the request to the router
     * Add the RouterMiddleware as the last piece of the stack
     * Send the Request through the stack
     * Pass exceptions to the exception handler
     *
     * @param  ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request) : ResponseInterface
    {
        try {
            $request = $this->getContainer()->get(Router::class)->dispatch($request);

            return $this->getContainer()->get(StackInterface::class)->resolve($request);
        } catch (\Exception $e) {
            return $this->getContainer()->get(ExceptionHandler::class)->handle($e);
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

        $this->configureContainer($builder);

        $this->container = $builder->build();
    }
}
