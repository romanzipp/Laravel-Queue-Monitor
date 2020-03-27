<?php

namespace romanzipp\QueueMonitor\Tests;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Orchestra\Testbench\TestCase as BaseTestCase;
use romanzipp\QueueMonitor\Providers\QueueMonitorProvider;
use romanzipp\QueueMonitor\Tests\Support\MonitoredJob;

class TestCase extends BaseTestCase
{
    use DatabaseMigrations;

    protected function dispatchNow(ShouldQueue $job)
    {
        app(Dispatcher::class)->dispatch(
            $job
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            QueueMonitorProvider::class,
        ];
    }
}
