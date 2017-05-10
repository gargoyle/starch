<?php

use Starch\App;

require('../vendor/autoload.php');

$app = new App();

$app->get('/', function($request, $response) {
    $response->getBody()->write('Hello, world!');

    return $response;
});

$app->add(function($request, $response, callable $next) {
    $response->getBody()->write(' Before1 ');
    $response = $next($request, $response);
    $response->getBody()->write(' After1 ');

    return $response;
});
$app->add(function($request, $response, callable $next) {
    $response->getBody()->write(' Before2 ');
    $response = $next($request, $response);
    $response->getBody()->write(' After2 ');

    return $response;
});

$app->run();
