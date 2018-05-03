<?php

namespace Starch\Tests\Integration;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;

class IntegrationTest extends TestCase
{
    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var Client
     */
    private $client;

    public function setUp()
    {
        $this->baseUrl = sprintf('http://%s:%s',
            getenv('INTEGRATION_TEST_SERVER_HOST'),
            getenv('INTEGRATION_TEST_SERVER_PORT')
        );

        $this->client = new Client();
    }

    public function testRunApp()
    {
        $response = $this->client->request('GET', $this->baseUrl . '/');
        
        $this->assertEquals('Hello, world!', (string)$response->getBody());
        $this->assertTrue($response->hasHeader('x-foo'));
    }

    public function testRouteRequestHandlerAsString()
    {
        $response = $this->client->request('GET', $this->baseUrl . '/foo');

        $this->assertEquals('foo', (string)$response->getBody());
    }

    public function testMethodNotAllowed()
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionCode(405);
        $this->client->request('POST', $this->baseUrl . '/');
    }
}
