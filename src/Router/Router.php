<?php

namespace Starch\Router;

use DI\InvokerInterface;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;
use Psr\Http\Message\ServerRequestInterface;
use Starch\Exception\HttpException;
use Starch\Exception\MethodNotAllowedException;
use Starch\Exception\NotFoundHttpException;

class Router
{
    /**
     * @var Route[]
     */
    private $routes = [];

    /**
     * @var InvokerInterface
     */
    private $invoker;

    public function __construct(InvokerInterface $invoker)
    {
        $this->invoker = $invoker;
    }

    /**
     * Create a new route
     *
     * @param array  $methods
     * @param string $path
     * @param mixed  $handler
     */
    public function map(array $methods, string $path, $handler) : void
    {
        $this->routes[] = new Route($methods, $path, $handler);
    }

    /**
     * Dispatches the request to the fast-router
     * Returns an enriched request with the proper attributes
     * Throws appropriate exceptions if the route isn't reachable
     *
     * @param  ServerRequestInterface $request
     *
     * @throws HttpException
     *
     * @return ServerRequestInterface
     */
    public function dispatch(ServerRequestInterface $request) : ServerRequestInterface
    {
        $dispatcher = simpleDispatcher(function(RouteCollector $r) {
            foreach ($this->routes as $index => $route) {
                $r->addRoute($route->getMethods(), $route->getPath(), $index);
            }
        });

        $routeInfo = $dispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());

        switch ($routeInfo[0]) {

            case Dispatcher::FOUND:
                $route = $this->routes[$routeInfo[1]];
                $route->setArguments($routeInfo[2]);

                return $request->withAttribute('route', $route);
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                throw new MethodNotAllowedException($request->getMethod(), $routeInfo[1]);
                break;
            case Dispatcher::NOT_FOUND:
            default:
                throw new NotFoundHttpException(sprintf("Route '%s' not found.", $request->getUri()->getPath()));
                break;
        }
    }
}
