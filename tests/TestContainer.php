<?php

namespace Starch\Tests;

use Exception;
use Invoker\Invoker;
use Invoker\InvokerInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Starch\Exception\ExceptionHandler;
use Starch\Router\Router;
use Starch\Router\RouterMiddleware;
use Zend\Diactoros\Response\EmitterInterface;
use Zend\Diactoros\Response\SapiEmitter;

class TestContainer implements ContainerInterface
{
    /**
     * @var array
     */
    private $dependencies = [];

    public function __construct()
    {
        $this->dependencies[EmitterInterface::class] = new SapiEmitter();
        $this->dependencies[ExceptionHandler::class] = new ExceptionHandler();
        $this->dependencies[InvokerInterface::class] = new Invoker(null, $this);
        $this->dependencies[Router::class] = new Router();
        $this->dependencies[RouterMiddleware::class] = new RouterMiddleware($this->dependencies[InvokerInterface::class]);
    }

    /**
     * @inheritdoc
     */
    public function get($id)
    {
        if ($this->has($id)) {
            return $this->dependencies[$id];
        }

        throw new class extends Exception implements NotFoundExceptionInterface {};
    }

    /**
     * @inheritdoc
     */
    public function has($id)
    {
        return array_key_exists($id, $this->dependencies);
    }
}
