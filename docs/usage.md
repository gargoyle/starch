# Installation and Usage

To install Starch, you need a minimum of two packages:

1. Starch itself
2. A PSR-11 compatible container.

If you want to use Starch' built in RouterMiddleware, you'll also need to install `php-di/invoker`.

```shell
composer install starchphp/starch
composer install php-di/php-di
```

Php-di was used as an example here. It comes with invoker as well, so there's no need to install it separately.

[Read more about setting up your container](containers.md).

## Simple usage

In it's simplest form, Starch can be used by creating an Application instance and adding middleware that are path 
constrained to resolve certain requests.

```php
// $container = ...

$app = new Application($container);

$app->add(function() {
    return new TextResponse('Hello, world!');
}, '/hello-world');

$app->run();
```

## Using the Router

Starch comes with a simple router based on [nikic' FastRoute](https://github.com/nikic/FastRoute). To use it, first add
routes to your app. At the end of your middleware chain, add the RouterMiddleware, which will take care of the rest.

```php
// $container = ...

$app = new Application($container);

$app->get('/hello-world', function() {
    return new TextResponse('Hello, world!');
});
$app->get('/hello/{name}', function(ServerRequestInterface $request) {
    return new TextResponse(sprintf(
        'Hello, %s!',
        $request->getAttribute('name')
    ));
});

//$app->add(...);
$app->add(RouterMiddleware::class);

$app->run();
```

At the start of the `Application::run()` method, the request will be matched to a Route. The RouterMiddleware will make 
sure the route handler will be called at the end of the middleware chain. A route handler MUST return a Response object. 

### Route handlers

Route handlers don't need to be closures. You can also use the FQN of an invokeable class or even a `callable` style 
array, e.g. `[WelcomeController::class, 'nameRequest']`. Note that in this case, `nameRequest` must not be a static 
function of the `WelcomeController` class. The Invoker used in the RouterMiddleware will fetch `WelcomeController` first
from your container and then execute the `nameRequest` on it. 
 
**Read more:**

- [Installation and Usage](docs/usage.md)
- [Using your own container](docs/containers.md)
- [Middleware](docs/middleware.md)
- [Components bound together by Starch](docs/components.md)
- [Comparison to other frameworks](docs/comparison.md)
