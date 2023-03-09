<?php

namespace romanzipp\QueueMonitor\Tests\TestCases;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\TestCase as BaseTestCase;
use romanzipp\QueueMonitor\Tests\Support\BaseJob;
use romanzipp\QueueMonitor\Tests\Support\TestQueueMonitorProvider;

abstract class TestCase extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->withoutMockingConsoleOutput();
        $this->withoutExceptionHandling();
    }

    protected function dispatch(BaseJob $job): self
    {
        app(Dispatcher::class)->dispatch($job);

        $this->assertQueueSize(1);

        return $this;
    }

    protected function assertDispatched(string $jobClass): self
    {
        $rows = DB::select('SELECT * FROM jobs');

        self::assertCount(1, $rows);
        self::assertEquals($jobClass, json_decode($rows[0]->payload)->displayName);

        return $this;
    }

    protected function assertQueueSize(int $size): self
    {
        self::assertSame($size, $this->app['queue']->connection()->size());

        return $this;
    }

    protected function workQueue(): void
    {
        $job = $this->app['queue']->connection($this->app['config']['queue.default']);

        if (null === $job) {
            self::fail('No job dispatched');
        }

        $this->artisan('queue:work --once');
    }

    protected function getPackageProviders($app): array
    {
        return [
            TestQueueMonitorProvider::class,
        ];
    }
}
