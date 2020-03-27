<?php

namespace romanzipp\QueueMonitor\Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use InvalidArgumentException;
use Orchestra\Testbench\TestCase as BaseTestCase;
use romanzipp\QueueMonitor\Providers\QueueMonitorProvider;
use romanzipp\QueueMonitor\Services\QueueMonitor;
use romanzipp\QueueMonitor\Tests\Support\BaseJob;

class TestCase extends BaseTestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        QueueMonitor::$loadMigrations = true;

        parent::setUp();

        $this->withoutMockingConsoleOutput();
        $this->withoutExceptionHandling();

        try {
            $this->artisan('queue:table');
            $this->artisan('migrate');
        } catch (InvalidArgumentException $e) {
            // TODO: this command fails locally but is required for travis ci
        }
    }

    protected function dispatch(BaseJob $job): void
    {
        dispatch($job);

        $this->artisan('queue:work --once');
    }

    protected function getPackageProviders($app)
    {
        return [
            QueueMonitorProvider::class,
        ];
    }
}
