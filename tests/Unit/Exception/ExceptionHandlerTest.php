<?php

namespace Starch\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Starch\Exception\ExceptionHandler;
use Starch\Exception\HttpException;

class ExceptionHandlerTest extends TestCase
{
    /**
     * @expectedException \LogicException
     */
    public function testThrowsNonHttpException()
    {
        $exception = new \LogicException();

        $handler = new ExceptionHandler();

        $handler->handle($exception);
    }

    public function testReturnsResponseOnHttpException()
    {
        $exception = new HttpException(500, 'foo');

        $handler = new ExceptionHandler();

        $response = $handler->handle($exception);

        $this->assertEquals('foo', (string)$response->getBody());
        $this->assertEquals(500, $response->getStatusCode());
    }
}
