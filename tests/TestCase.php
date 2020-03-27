<?php

namespace romanzipp\QueueMonitor\Tests;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Queue\QueueManager;
use Orchestra\Testbench\TestCase as BaseTestCase;
use romanzipp\QueueMonitor\Providers\QueueMonitorProvider;
use romanzipp\QueueMonitor\QueueMonitorHandler;

class TestCase extends BaseTestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        QueueMonitorHandler::$loadMigrations=true;

        parent::setUp();
    }

    protected function dispatch(ShouldQueue $job)
    {
        app(QueueManager::class)->push($job);

        // Not attaching Job
        // app(Dispatcher::class)->dispatch($job);
    }

    protected function getPackageProviders($app)
    {
        return [
            QueueMonitorProvider::class,
        ];
    }
}
