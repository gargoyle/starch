<?php

namespace Starch;

use Closure;
use Interop\Http\Server\MiddlewareInterface;
use mindplay\middleman\ContainerResolver;
use mindplay\middleman\Dispatcher;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Starch\Exception\ExceptionHandler;
use Starch\Middleware\Middleware;
use Starch\Router\Router;
use Zend\Diactoros\Response\EmitterInterface;
use Zend\Diactoros\ServerRequestFactory;

class Application
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Middleware[]
     */
    private $middleware;

    public function __construct(ContainerInterface $container)
    {
        $this->verifyContainer($container);
        $this->container = $container;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * Add all middleware to one array, it will be filtered based on the route once a request is being processed.
     *
     * @param Closure|MiddlewareInterface|string $middleware
     * @param string|null $pathConstraint
     *
     * @return void
     */
    public function add($middleware, string $pathConstraint = null): void
    {
        $this->middleware[] = new Middleware($middleware, $pathConstraint);
    }

    /********************************************************************************
     * Router proxy methods
     *******************************************************************************/

    /**
     * Add a GET route
     *
     * @param string $path
     * @param mixed $handler
     *
     * @return void
     */
    public function get(string $path, $handler): void
    {
        $this->map(['GET'], $path, $handler);
    }

    /**
     * Add a POST route
     *
     * @param string $path
     * @param mixed $handler
     *
     * @return void
     */
    public function post(string $path, $handler): void
    {
        $this->map(['POST'], $path, $handler);
    }

    /**
     * Add a PUT route
     *
     * @param string $path
     * @param mixed $handler
     *
     * @return void
     */
    public function put(string $path, $handler): void
    {
        $this->map(['PUT'], $path, $handler);
    }

    /**
     * Add a PATCH route
     *
     * @param string $path
     * @param mixed $handler
     *
     * @return void
     */
    public function patch(string $path, $handler): void
    {
        $this->map(['PATCH'], $path, $handler);
    }

    /**
     * Add a DELETE route
     *
     * @param string $path
     * @param mixed $handler
     *
     * @return void
     */
    public function delete(string $path, $handler): void
    {
        $this->map(['DELETE'], $path, $handler);
    }

    /**
     * Map multiple methods for a route
     *
     * @param string[] $methods
     * @param string $path
     * @param mixed $handler
     *
     * @return void
     */
    public function map(array $methods, string $path, $handler): void
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
     *
     * @codeCoverageIgnore
     */
    public function run(): void
    {
        $request = $request = ServerRequestFactory::fromGlobals();

        $response = $this->process($request);

        $this->getContainer()->get(EmitterInterface::class)->emit($response);
    }

    /**
     * Dispatch the request to the router
     * Filter the middleware according to the route
     * Dispatch routed request to middleman to process middleware
     * Pass exceptions to the exception handler
     *
     * @param  ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $request = $this->getContainer()->get(Router::class)->dispatch($request);

            $filteredMiddleware = [];
            foreach ($this->middleware as $middleware) {
                if ($middleware->executeFor($request->getAttribute('route'))) {
                    $filteredMiddleware[] = $middleware->getMiddleware();
                }
            }

            $dispatcher = new Dispatcher(
                $filteredMiddleware,
                new ContainerResolver($this->getContainer())
            );

            return $dispatcher->dispatch($request);
        } catch (\Exception $e) {
            return $this->getContainer()->get(ExceptionHandler::class)->handle($e);
        }
    }

    /********************************************************************************
     * Private methods
     *******************************************************************************/

    /**
     * Verify that all dependencies required by Starch are present
     *
     * @return void
     */
    private function verifyContainer(ContainerInterface $container): void
    {
        $requiredDependencies = [
            EmitterInterface::class,
            ExceptionHandler::class,
            Router::class,
        ];

        foreach ($requiredDependencies as $requiredDependency) {
            if (!$container->has($requiredDependency)) {
                throw new RuntimeException(sprintf(
                    'Dependency "%s" needs to be available in your provided container.',
                    $requiredDependency
                ));
            }
        }
    }
}
