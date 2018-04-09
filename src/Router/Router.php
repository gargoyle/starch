<?php

namespace Starch\Router;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
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
     * @param RequestHandlerInterface|string $handler
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
     * @return ServerRequestInterface
     */
    public function dispatch(ServerRequestInterface $request): ServerRequestInterface
    {
        $dispatcher = simpleDispatcher([$this, 'addRoutes']);

        $routeInfo = $dispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());

        if ($routeInfo[0] === Dispatcher::FOUND) {
            $request = $this->processFoundRequest($routeInfo, $request);
        }

        $requestHandler = $this->getRequestHandler($routeInfo, $request);

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
    private function processFoundRequest(array $routeInfo, ServerRequestInterface $request): ServerRequestInterface {
        $route = $this->routes[$routeInfo[1]];

        foreach ($routeInfo[2] as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        $request = $request->withAttribute('route', $route);

        return $request;
    }

    /**
     * @param array $routeInfo
     * @param ServerRequestInterface $request
     *
     * @return RequestHandlerInterface|string
     */
    private function getRequestHandler(array $routeInfo, ServerRequestInterface $request)
    {
        switch ($routeInfo[0]) {
            case Dispatcher::FOUND:
                return $request->getAttribute('route')->getHandler();
            case Dispatcher::METHOD_NOT_ALLOWED:
                return new MethodNotAllowedRequestHandler($routeInfo[1]);
            case Dispatcher::NOT_FOUND:
            default:
                return new NotFoundRequestHandler();
        }
    }
}
