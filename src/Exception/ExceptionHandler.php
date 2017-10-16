<?php

namespace Starch\Exception;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\HtmlResponse;

class ExceptionHandler
{
    /**
     * Transforms exceptions into acceptable responses
     * Exceptions that can't be handled will be thrown again
     *
     * @param Exception $exception
     *
     * @throws Exception
     *
     * @return ResponseInterface
     */
    public function handle(Exception $exception): ResponseInterface
    {
        if ($exception instanceof HttpException) {
            return new HtmlResponse($exception->getMessage(), $exception->getStatusCode());
        }

        throw $exception;
    }
}
