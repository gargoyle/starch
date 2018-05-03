<?php

namespace Starch\Exception;

use Throwable;

class MethodNotAllowedException extends HttpException
{
    public function __construct(string $method, array $allowedMethods, Throwable $previous = null)
    {
        parent::__construct(
            405,
            sprintf(
                "Method '%s' not allowed. Allowed: %s",
                strtoupper($method),
                implode(', ', array_map('strtoupper', $allowedMethods))
            ),
            $previous
        );
    }
}
