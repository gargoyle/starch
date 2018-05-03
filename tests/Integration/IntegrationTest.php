<?php

namespace Starch\Tests\Integration;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

class IntegrationTest extends TestCase
{
    public function testRunApp()
    {
        $client = new Client();

        $url = sprintf('http://%s:%s/',
            getenv('INTEGRATION_TEST_SERVER_HOST'),
            getenv('INTEGRATION_TEST_SERVER_PORT')
        );

        $response = $client->request('GET', $url);
        
        $this->assertEquals('Hello, world!', (string)$response->getBody());
        $this->assertTrue($response->hasHeader('x-foo'));
    }

    public function testRouteRequestHandlerAsString()
    {
        $client = new Client();

        $url = sprintf('http://%s:%s/foo',
            getenv('INTEGRATION_TEST_SERVER_HOST'),
            getenv('INTEGRATION_TEST_SERVER_PORT')
        );

        $response = $client->request('GET', $url);

        $this->assertEquals('foo', (string)$response->getBody());
    }
}
