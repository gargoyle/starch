<?php

namespace Starch\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Starch\Exception\MethodNotAllowedException;

class MethodNotAllowedExceptionTest extends TestCase
{
    public function testFormatsMessage()
    {
        $exception = new MethodNotAllowedException('GET', ['POST', 'PUT']);

        $this->assertEquals("Method 'GET' not allowed. Allowed: POST, PUT", $exception->getMessage());
        $this->assertEquals(405, $exception->getStatusCode());
    }
}
