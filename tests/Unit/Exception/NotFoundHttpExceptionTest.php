<?php

namespace Starch\Tests\Unit\Exception;

use Starch\Exception\NotFoundHttpException;
use PHPUnit\Framework\TestCase;

class NotFoundHttpExceptionTest extends TestCase
{
    public function testUsesCorrectStatusCode()
    {
        $exception = new NotFoundHttpException('not found');

        $this->assertEquals(404, $exception->getStatusCode());
        $this->assertEquals('not found', $exception->getMessage());
    }
}
