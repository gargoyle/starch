<?php

namespace Starch\Exception;

use RuntimeException;
use Throwable;

class HttpException extends RuntimeException
{
    public function __construct(int $statusCode, string $message, Throwable $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
    }
}
