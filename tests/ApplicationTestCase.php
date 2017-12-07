<?php

namespace Starch\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Starch\Application;
use Zend\Diactoros\ServerRequestFactory;

class ApplicationTestCase extends TestCase
{
    /**
     * @var Application
     */
    protected $app;

    /*
     * Override this method in your tests with your own App, if you have one.
     */
    public function setUp()
    {
        $this->app = new Application(new TestContainer());
    }

    /**
     * Send a mock request to the App
     *
     * @param  string $method
     * @param  string $path
     * @param  array $body
     * @param  array $headers
     *
     * @return ResponseInterface
     */
    public function request($method, $path, $body = [], $headers = [])
    {
        $uri = $path;
        $query = '';
        if (false !== strpos($uri, '?')) {
            [$uri, $query] = explode('?', $path);
        }

        $request = ServerRequestFactory::fromGlobals(
            [
                'SERVER_PROTOCOL'      => 'HTTP/1.1',
                'REQUEST_METHOD'       => strtoupper($method),
                'REQUEST_URI'          => $uri,
                'QUERY_STRING'         => $query,
                'SERVER_NAME'          => 'localhost',
                'SERVER_PORT'          => 80,
            ] + $headers,
            null,
            $body,
            null,
            null
        );

        return $this->app->process($request);
    }

    public function get($path, $headers = [])
    {
        return $this->request('GET', $path, null, $headers);
    }

    public function post($path, $body = null, $headers = [])
    {
        return $this->request('POST', $path, $body, $headers);
    }

    public function put($path, $body = null, $headers = [])
    {
        return $this->request('PUT', $path, $body, $headers);
    }

    public function patch($path, $body = null, $headers = [])
    {
        return $this->request('PATCH', $path, $body, $headers);
    }

    public function delete($path, $headers = [])
    {
        return $this->request('DELETE', $path, null, $headers);
    }
}
