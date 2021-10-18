<?php

namespace romanzipp\QueueMonitor\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\TestCase as BaseTestCase;
use romanzipp\QueueMonitor\Providers\QueueMonitorProvider;
use romanzipp\QueueMonitor\Tests\Support\BaseJob;

class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->withoutMockingConsoleOutput();
        $this->withoutExceptionHandling();
    }

    protected function defineDatabaseMigrations()
    {
        try {
            $this->artisan('queue:table');
            $this->artisan('migrate');
        } catch (\InvalidArgumentException $exception) {
        }

        try {
            $this->artisan('queue:failed-table');
        } catch (\InvalidArgumentException $exception) {
        }

        $this->loadMigrationsFrom(__DIR__ . '/../migrations');
    }

    protected function dispatch(BaseJob $job): self
    {
        dispatch($job);

        return $this;
    }

    protected function assertDispatched(string $jobClass): self
    {
        $rows = DB::select('SELECT * FROM jobs');

        self::assertCount(1, $rows);
        self::assertEquals($jobClass, json_decode($rows[0]->payload)->displayName);

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
