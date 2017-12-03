# Starch


[![Build Status](https://img.shields.io/travis/starchphp/starch.svg?style=flat-square)](https://travis-ci.org/starchphp/starch)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/starchphp/starch.svg?style=flat-square)](https://scrutinizer-ci.com/g/starchphp/starch/?branch=master)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/starchphp/starch.svg?style=flat-square)](https://scrutinizer-ci.com/g/starchphp/starch/?branch=master)

Starch binds a bunch of PSR compatible components together to form a functioning micro-framework.

<p align="center">
<img src="https://imgur.com/0RtqG1Q.png" />
</p>

<small>Logo by [Pencil](https://thenounproject.com/pencil/) from Noun project</small>

## Installation

Require the package with composer

```bash
composer require starchphp/starch
```

## Usage


Create a new App, define routes, add middlewares and run the app.

```php
<?php

use Interop\Http\Server\RequestHandlerInterface;
use Starch\App;
use Starch\Router\RouterMiddleware;
use Zend\Diactoros\Response;

require('../vendor/autoload.php');

$app = new App();

$app->get('/', function() {
    $response = new Response();
    $response->getBody()->write('Hello');

    return $response;
});
$app->get('/hello/{name}', function($request, $name) {
    $response = new Response();
    $response->getBody()->write(sprintf('Hello, %s!', $name));

    return $response;
});

$app->add(function($request, RequestHandlerInterface $next) {
    $response = $next->handle($request);
    $response->getBody()->write(', world! ');

    return $response;
});
$app->add(function($request, RequestHandlerInterface $delegate) {
    $response = $delegate->handle($request);

    $response->getBody()->write(' How are you?');

    return $response;
}, '/hello');

$app->add(RouterMiddleware::class);

$app->run();

```

#### Constraining middleware to certain paths

The `App::add` method has an optional second `$pathConstraint` parameter. Add a (part of a) route path here to limit the
 execution of this middleware to this route. 

#### RouterMiddleware
The RouterMiddleware is not added by default. You should add it yourself at the end of the stack or add one of your own.

### Container

By default, Starch uses php-di as it's container. If you want to extend the App to your own, you can override 
`App::configureContainer` to add your own dependencies. Make sure to either call `parent::configureContainer` or make sure 
you define the required definitions (see lower).

If you want, you can use another PSR-11 compatible container with Starch, simply pass it as a parameter when creating the app:
 
 ```php
 $app = new App($myContainer);
 ```

#### Required definitions

You must add the following services to your container:

- `Invoker\InvokerInterface`: [PHP-DI/Invoker](https://github.com/PHP-DI/Invoker) is recommended.
- `Zend\Diactoros\Response\EmitterInterface`: The built in `Zend\Diactoros\Response\SapiEmitter` is recommended.

Aditionally, if your container does not support auto-wiring, the following should be defined as an instance of themselves as well:

- `Starch\Router\Router`
- `Starch\Exception\ExceptionHandler`
- `Starch\Router\RouterMiddleware`

## Components used

The following components are used to provide a coherent whole

- [zendframework/zend-diactoros](https://github.com/zendframework/zend-diactoros) provides the PSR-7 interfaces
- [php-di/php-di](https://github.com/PHP-DI/PHP-DI) provides a PSR-11 container
- [nikic/fast-route](https://github.com/nikic/FastRoute) is used for the routing

## How it works

- You construct a new App (optionally extending it to configure your own container services)
- You add routes (which get added to the Router) and middlewares (which get added to the Stack)
- You call `app->run()`, which will construct a request from globals, process the request and emit the returned response
- To process the request to get a response:
    - It's dispatched to our Router, which calls nikic's FastRouter. If the request matches a route, the request gets tagged with the route as attribute and is returned.
    - Upon errors in the dispatcher, appropriate HttpExceptions are thrown
    - Next, the request (now enriched with route) is sent to the middleware stack.
    - The stack will return Delegates that call the middlewares in a First In, First Out order.
    - Once the stack is empty, it will return one last Delegate that actually calls the route handler (with potential route arguments)
    
## Comparison with other frameworks
 
 Other micro frameworks exist that handle middleware too. Here's a comparison explaining why and where Starch is different:
 
 |                                                           |                                                                            Slim                                                                           |                                      Silex                                     |              Starch             |
 |-----------------------------------------------------------|:---------------------------------------------------------------------------------------------------------------------------------------------------------:|:------------------------------------------------------------------------------:|:-------------------------------:|
 |        Single `->add()` interface to add middleware       |                                                                            Yes                                                                            |                No  (separate `->before()` and `->after()` calls)               |               Yes               |
 | Unified queue with full control over middleware execution |              No, outer middleware are app-bound, then follow route group middleware, then route middleware as inner most layers of the stack.             |                                       yes                                      |               Yes               |
 |                 Middleware execution order                |                                                                 FILO (First In, Last Out)                                                                 |            FIFO (First In, First Out) with optional priority control           |               FIFO              |
 |                    Middleware interface                   | "Double pass" See [PSR-15 meta discussion](https://github.com/php-fig/fig-standards/blob/master/proposed/http-middleware/middleware-meta.md#4-approaches) | Separate interfaces depending on wether it's a `before` or `after` middleware. | PSR-15 compatible "single pass" |

