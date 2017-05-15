<?php

namespace Starch\Tests\Unit\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Starch\Middleware\Delegate;
use PHPUnit\Framework\TestCase;

class DelegateTest extends TestCase
{
    public function testCallsClosureWithRequest()
    {
        $mockRequest = $this->createMock(ServerRequestInterface::class);

        $delegate = new Delegate(function($request) use ($mockRequest) {
            $this->assertEquals($mockRequest, $request);

            return $this->createMock(ResponseInterface::class);
        });

        $delegate->process($mockRequest);
    }
}
