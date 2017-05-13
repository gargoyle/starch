<?php

use Interop\Http\ServerMiddleware\DelegateInterface;
use Starch\App;
use Zend\Diactoros\Response;

require('../vendor/autoload.php');

$app = new App();

$app->get('/', function() {
    $response = new Response();

    $response->getBody()->write('Hello, world!');

    return $response;
});

$app->add(function($request, DelegateInterface $next) {
    $response = $next->process($request);
    $response->getBody()->write(' After1 ');

    return $response;
});
$app->add(function($request, DelegateInterface $next) {
    $response = $next->process($request);
    $response->getBody()->write(' After2 ');

    return $response;
});

$app->run();
