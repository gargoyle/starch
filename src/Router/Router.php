<?php

namespace Starch\Router;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;
use Psr\Http\Message\RequestInterface;
use Starch\Exception\MethodNotAllowedException;
use Starch\Exception\NotFoundHttpException;

class Router
{
    /**
     * @var Route[]
     */
    private $routes = [];

    public function map(array $methods, string $route, $handler)
    {
        $this->routes[] = new Route($methods, $route, $handler);
    }

    public function dispatch(RequestInterface $request)
    {
        $dispatcher = simpleDispatcher(function(RouteCollector $r) {
            foreach ($this->routes as $route) {
                $r->addRoute($route->getMethods(), $route->getRoute(), $route->getHandler());
            }
        });

        $routeInfo = $dispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());

        switch ($routeInfo[0]) {
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                // $vars = $routeInfo[2];
                return $handler;
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
