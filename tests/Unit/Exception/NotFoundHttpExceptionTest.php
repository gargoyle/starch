<?php

namespace Starch\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Starch\Exception\NotFoundHttpException;

class NotFoundHttpExceptionTest extends TestCase
{
    public function testUsesCorrectStatusCode()
    {
        $exception = new NotFoundHttpException('not found');

        $this->assertEquals(404, $exception->getCode());
        $this->assertEquals('not found', $exception->getMessage());
    }
}
