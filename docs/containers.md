# Using your own container

While developing Starch, I originally intended to couple it with PHP-DI's excellent container. However, I quickly became 
aware that even though containers nowadays often all implement PSR-11, setting them up happens very differently and 
people tend to have strong personal preferences about which container implementation they like to use.

That is why Starch makes no assumption about which container you wish to use. It only requires PSR-11 compatibility. 
Packagist provides a good [list of containers that implement psr/container](https://packagist.org/providers/psr/container-implementation).

I personally still recommend [PHP-DI](https://packagist.org/packages/php-di/php-di) and it'll be the container that's 
used in further code examples.

### Required dependencies

The following dependencies are required to be set in your container implementation for Starch to work:

- Zend\Diactoros\Response\EmitterInterface, this will likely be a SapiEmitter implementation
- Starch\Exception\ExceptionHandler
- Starch\Router\Router

For PHP-DI, the base configuration would look a little something like:

```php
use function DI\object;
use DI\ContainerBuilder;
use Starch\Exception\ExceptionHandler;
use Starch\Router\Router;
use Zend\Diactoros\Response\EmitterInterface;

$builder = ContainerBuilder();

$builder->addDefinitions([
    EmitterInterface::class => object(SapiEmitter::class),
    
    ExceptionHandler::class => object(),
    
    Router::class => object(),
]);

$container = $builder->build();
```

If you want to use Starch' built in RouterMiddleware, you must make that available in the container as well, together 
with it's Invoker dependency.

```php 
use function DI\get;
use DI\Container;
use Invoker\InvokerInterface;
use Starch\Router\RouterMiddleware;

$builder->addDefinitions([
    // ... 
    
    InvokerInterface::class => get(Container $container),
    
    RouterMiddleware::class => object()
        ->constructor(get(InvokerInterface::class)),
]);
```

PHP-DI's container implements the InvokerInterface on it's own. If you're using a different container, you can create an
Invoker instance like so:

```php
// Build your own PSR-11 container
// $container = ...

$invoker = new \Invoker\Invoker(null, $container); 
```

More information about Invoker can be found on [the project's GitHub](https://github.com/PHP-DI/Invoker).

**Read more:**

- [Installation and Usage](docs/usage.md)
- [Using your own container](docs/containers.md)
- [Middleware](docs/middleware.md)
- [Components bound together by Starch](docs/components.md)
- [Comparison to other frameworks](docs/comparison.md)
