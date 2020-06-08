<?php

namespace romanzipp\QueueMonitor\Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
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

    protected function dispatch(BaseJob $job): self
    {
        dispatch($job);

        return $this;
    }

    protected function assertDispatched(string $jobClass): self
    {
        $rows = DB::select('SELECT * FROM jobs');

        $this->assertCount(1, $rows);
        $this->assertEquals($jobClass, json_decode($rows[0]->payload)->displayName);

        return $this;
    }

    protected function workQueue(): void
    {
        $this->artisan('queue:work --once --sleep 1');
    }

    protected function getPackageProviders($app)
    {
        return [
            QueueMonitorProvider::class,
        ];
    }
}
