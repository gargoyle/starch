<?php

namespace Starch\Tests\Integration;

use GuzzleHttp\Client;
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
}
