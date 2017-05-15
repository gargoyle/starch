<?php

namespace Starch\Exception;

use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\HtmlResponse;

class ExceptionHandler
{
    public function handle(\Exception $exception) : ResponseInterface
    {
        switch (true) {
            case $exception instanceof HttpException:
                return new HtmlResponse($exception->getMessage(), $exception->getStatusCode());
        }

        throw $exception;
    }
}
