<?php

namespace Starch;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Starch\Exception\HttpException;
use Starch\Middleware\Middleware;
use Starch\Middleware\NextHandler;
use Starch\Middleware\Stack;
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
    private $middleware = [];

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
     * @param MiddlewareInterface|string $middleware
     * @param string|null $pathConstraint
     *
     * @return void
     */
    public function add($middleware, string $pathConstraint = null): void
    {
        if (is_string($middleware)) {
            $middleware = $this->getContainer()->get($middleware);
        }

        if (! $middleware instanceof MiddlewareInterface) {
            throw new \InvalidArgumentException('Middleware must be an instance of ' . MiddlewareInterface::class);
        }

        $this->middleware[] = new Middleware($middleware, $pathConstraint);
    }

    /********************************************************************************
     * Router proxy methods
     *******************************************************************************/

    /**
     * Add a GET route
     *
     * @param string $path
     * @param RequestHandlerInterface|string $handler
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
     * @param RequestHandlerInterface|string $handler
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
     * @param RequestHandlerInterface|string $handler
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
     * @param RequestHandlerInterface|string $handler
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
     * @param RequestHandlerInterface|string $handler
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
     * @param RequestHandlerInterface|string $handler
     *
     * @return void
     */
    public function map(array $methods, string $path, $handler): void
    {
        if (is_string($handler)) {
            $handler = $this->getContainer()->get($handler);
        }

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
     * Process the request into a response
     *
     * @param  ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request): ResponseInterface
    {
        $filteredMiddleware = $this->middleware;

        try {
            $request = $this->getContainer()->get(Router::class)->dispatch($request);

            $route = $request->getAttribute('route');
            $filteredMiddleware = array_filter($filteredMiddleware, function(Middleware $middleware) use ($route) {
                return $middleware->executeFor($route);
            });
            $requestHandler = $route->getHandler();
        } catch (HttpException $e) {
            $requestHandler = new NextHandler(function() use ($e) {
                throw $e;
            });
        }

        $filteredMiddleware = array_map(function(Middleware $middleware) {
            return $middleware->getMiddleware();
        }, $filteredMiddleware);


        $dispatcher = new Stack(
            $filteredMiddleware,
            $requestHandler
        );

        return $dispatcher->dispatch($request);
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
