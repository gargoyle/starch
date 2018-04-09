# Using your own container

Starch makes no assumption about which container you wish to use. It only requires PSR-11 compatibility. Packagist 
provides a good [list of containers that implement psr/container](https://packagist.org/providers/psr/container-implementation).

We recommend [PHP-DI](https://packagist.org/packages/php-di/php-di) and it'll be the container that's used in further 
code examples.

### Required dependencies

The following dependencies are required to be set in your container implementation for Starch to work:

- Zend\Diactoros\Response\EmitterInterface, this will likely be a SapiEmitter implementation
- Starch\Router\Router

For PHP-DI, the base configuration would look a little something like:

```php
use function DI\object;
use DI\ContainerBuilder;
use Starch\Router\Router;
use Zend\Diactoros\Response\EmitterInterface;

$builder = ContainerBuilder();

$builder->addDefinitions([
    EmitterInterface::class => object(SapiEmitter::class),
    
    Router::class => object(),
]);

$container = $builder->build();
```

**Read more:**

- [Usage](docs/usage.md)
- [Using your own container](docs/containers.md)
- [Middleware](docs/middleware.md)
- [Components bound together by Starch](docs/components.md)
- [Comparison to other frameworks](docs/comparison.md)
