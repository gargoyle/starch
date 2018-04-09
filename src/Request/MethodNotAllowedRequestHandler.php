<?php

namespace Starch\Request;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Starch\Exception\MethodNotAllowedException;

class MethodNotAllowedRequestHandler implements RequestHandlerInterface
{
    /**
     * @var string[]
     */
    private $allowedMethods;

    public function __construct(array $allowedMethods)
    {
        $this->allowedMethods = $allowedMethods;
    }

    /**
     * @inheritdoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        throw new MethodNotAllowedException($request->getMethod(), $this->allowedMethods);
    }
}
