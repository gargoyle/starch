<?php

use Psr\Http\Message\ResponseInterface;

require('../vendor/autoload.php');

$app = new \Starch\App();

$app->add(function($request, ResponseInterface $response, callable $next) {
    $response->getBody()->write(' Before1 ');
    $response = $next($request, $response);
    $response->getBody()->write(' After1 ');

    return $response;
});
$app->add(function($request, ResponseInterface $response, callable $next) {
    $response->getBody()->write(' Before2 ');
    $response = $next($request, $response);
    $response->getBody()->write(' After2 ');

    return $response;
});


$app->run();



