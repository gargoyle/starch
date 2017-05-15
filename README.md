# starchphp/starch


[![Build Status](https://img.shields.io/travis/starchphp/starch.svg?style=flat-square)](https://travis-ci.org/starchphp/starch)

Starch binds a bunch of PSR compatible components together to form a functioning micro-framework.

## Installation

Require the package with composer

```bash
composer require starchphp/starch
```

## Usage


Create a new App, define routes, add middlewares and run the app.

```php
<?php

use Interop\Http\ServerMiddleware\DelegateInterface;
use Starch\App;
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

$app->add(function($request, DelegateInterface $next) {
    $response = $next->process($request);
    $response->getBody()->write(', world! ');

    return $response;
}, '/');
$app->add(function($request, DelegateInterface $delegate) {
    $response = $delegate->process($request);

    $response->getBody()->write(' How are you?');

    return $response;
}, '/hello.+');

$app->run();

```

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
    