<?php

namespace Starch\Router;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Psr\Http\Message\ServerRequestInterface;
use Starch\Exception\HttpException;
use Starch\Exception\MethodNotAllowedException;
use Starch\Exception\NotFoundHttpException;
use function FastRoute\simpleDispatcher;

class Router
{
    /**
     * @var Route[]
     */
    private $routes = [];

    /**
     * Create a new route
     *
     * @param array $methods
     * @param string $path
     * @param mixed $handler
     *
     * @return Route
     */
    public function map(array $methods, string $path, $handler): Route
    {
        return $this->routes[] = new Route($methods, $path, $handler);
    }

    /**
     * Dispatches the request to the fast-router
     * Returns an enriched request with the proper attributes
     * Throws appropriate exceptions if the route isn't reachable
     *
     * @param ServerRequestInterface $request
     *
     * @throws HttpException
     *
     * @return ServerRequestInterface
     */
    public function dispatch(ServerRequestInterface $request): ServerRequestInterface
    {
        $dispatcher = simpleDispatcher([$this, 'addRoutes']);

        $routeInfo = $dispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());

        switch ($routeInfo[0]) {
            case Dispatcher::FOUND:
                $route = $this->routes[$routeInfo[1]];
                foreach ($routeInfo[2] as $key => $value) {
                    $request = $request->withAttribute($key, $value);
                }

                return $request->withAttribute('route', $route);
            case Dispatcher::METHOD_NOT_ALLOWED:
                throw new MethodNotAllowedException($request->getMethod(), $routeInfo[1]);
            case Dispatcher::NOT_FOUND:
            default:
                throw new NotFoundHttpException(sprintf("Route '%s' not found.", $request->getUri()->getPath()));
        }
    }

    /**
     * Callable for simpleDispatcher to add routes
     *
     * @param RouteCollector $routeCollector
     *
     * @return void
     */
    public function addRoutes(RouteCollector $routeCollector): void
    {
        foreach ($this->routes as $index => $route) {
            $routeCollector->addRoute($route->getMethods(), $route->getPath(), $index);
        }
    }
}
