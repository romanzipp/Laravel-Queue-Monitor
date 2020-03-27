<?php

namespace romanzipp\QueueMonitor\Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
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
    }

    protected function dispatch(BaseJob $job): void
    {
        dispatch($job);

        $this->artisan('queue:work --once')->run();
    }

    protected function getPackageProviders($app)
    {
        return [
            QueueMonitorProvider::class,
        ];
    }
}
