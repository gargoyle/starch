<?php

namespace Starch\Request;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Starch\Exception\NotFoundHttpException;

class NotFoundRequestHandler implements RequestHandlerInterface
{
    /**
     * @inheritdoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        throw new NotFoundHttpException(sprintf("Route '%s' not found.", $request->getUri()->getPath()));
    }
}