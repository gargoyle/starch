<?php

namespace Starch\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Starch\Exception\HttpException;

class HttpExceptionTest extends TestCase
{
    public function testKeepsStatusCode()
    {
        $exception = new HttpException(418, "I'M A TEAPOT");

        $this->assertEquals(418, $exception->getStatusCode());
        $this->assertEquals("I'M A TEAPOT", $exception->getMessage());
    }
}
