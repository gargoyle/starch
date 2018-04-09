<?php

namespace Starch\Router;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Psr\Http\Message\ServerRequestInterface;
use Starch\Exception\HttpException;
use Starch\Request\MethodNotAllowedRequestHandler;
use Starch\Request\NotFoundRequestHandler;
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

                $request = $this->setRouteAttributes($routeInfo, $request);
                $request = $request->withAttribute('route', $route);

                $requestHandler = $route->getHandler();
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $requestHandler = new MethodNotAllowedRequestHandler($routeInfo[1]);
                break;
            case Dispatcher::NOT_FOUND:
            default:
                $requestHandler = new NotFoundRequestHandler();
        }

        return $request->withAttribute('requestHandler', $requestHandler);
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

    /**
     * Uses the routeInfo from FastRoute to set attributes on the request
     *
     * @param array $routeInfo
     * @param ServerRequestInterface $request
     *
     * @return ServerRequestInterface
     */
    private function setRouteAttributes(array $routeInfo, ServerRequestInterface $request): ServerRequestInterface {
        foreach ($routeInfo[2] as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        return $request;
    }
}
