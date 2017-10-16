<?php

namespace Starch\Tests\Integration;

use PHPUnit\Framework\BaseTestListener;
use PHPUnit\Framework\TestSuite;

class IntegrationListener extends BaseTestListener
{
    /**
     * @var int PID of PHP process
     */
    private $pid;

    public function startTestSuite(TestSuite $suite)
    {
        if ('Integration' !== $suite->getName()) {
            return;
        }

        // Command that starts the built-in web server
        $command = sprintf(
            'php -S %s:%d -t %s >/dev/null 2>&1 & echo $!',
            getenv('INTEGRATION_TEST_SERVER_HOST'),
            getenv('INTEGRATION_TEST_SERVER_PORT'),
            __DIR__
        );

        // Execute the command and store the process ID
        $output = [];
        exec($command, $output);
        $this->pid = (int)$output[0];

        echo PHP_EOL
            . sprintf(
                '%s - Web server started on %s:%d with PID %d',
                date('r'),
                getenv('INTEGRATION_TEST_SERVER_HOST'),
                getenv('INTEGRATION_TEST_SERVER_PORT'),
                $this->pid
            )
            . PHP_EOL;

        usleep(500000);
    }

    public function endTestSuite(TestSuite $suite)
    {
        if ('Integration' !== $suite->getName()) {
            return;
        }

        echo PHP_EOL
            . sprintf('%s - Killing process with ID %d', date('r'), $this->pid)
            . PHP_EOL;

        exec('kill ' . $this->pid);
    }
}
