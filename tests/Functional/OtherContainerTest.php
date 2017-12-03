<?php

namespace Starch\Tests\Functional;

use DI\NotFoundException;
use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Invoker\Invoker;
use Invoker\InvokerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Starch\App;
use Starch\Exception\ExceptionHandler;
use Starch\Router\Router;
use Starch\Router\RouterMiddleware;
use Starch\Tests\AppTestCase;
use Zend\Diactoros\Response;

class OtherContainerTest extends AppTestCase
{
    public function setUp()
    {
        $this->app = new App(new OtherContainer());
    }

    public function testAcceptsCustomContainer()
    {
        $this->app->get('/', function() {
            $response = new Response();
            $response->getBody()->write('foo');

            return $response;
        });
        $this->app->add(new StubMiddleware());
        $this->app->add(RouterMiddleware::class);

        $response = $this->get('/');

        $this->assertEquals('foobar', (string)$response->getBody());
    }
}

class OtherContainer implements ContainerInterface
{
    private $services = [];

    public function __construct()
    {
        $this->services[InvokerInterface::class] = new Invoker(null, $this);
        $this->services[Router::class] = new Router();
        $this->services[ExceptionHandler::class] = new ExceptionHandler();
        $this->services[RouterMiddleware::class] = new RouterMiddleware($this->services[InvokerInterface::class]);
    }

    public function get($id)
    {
        if ($this->has($id)) {
            return $this->services[$id];
        }

        throw new NotFoundException(sprintf('Service %s not set in container', $id));
    }

    public function has($id)
    {
        return array_key_exists($id, $this->services);
    }
}

class StubMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $response->getBody()->write('bar');

        return $response;
    }
}
