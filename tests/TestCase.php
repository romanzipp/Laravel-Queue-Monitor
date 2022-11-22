<?php

namespace romanzipp\QueueMonitor\Tests;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\Concerns\CreatesApplication;
use romanzipp\QueueMonitor\Providers\QueueMonitorProvider;
use romanzipp\QueueMonitor\Tests\Support\BaseJob;

class TestCase extends BaseTestCase
{
    use RefreshDatabase;
    use CreatesApplication;

    public function setUp(): void
    {
        parent::setUp();

        $this->withoutMockingConsoleOutput();
        $this->withoutExceptionHandling();
    }

    protected function refreshTestDatabase()
    {
        rescue(fn () => $this->artisan('queue:table'));
        rescue(fn () => $this->artisan('queue:failed-table'));
        rescue(fn () => $this->artisan('migrate'));

        parent::refreshTestDatabase();
    }

    protected function dispatch(BaseJob $job): self
    {
        app(Dispatcher::class)->dispatch($job);
        // dispatch($job);

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

    protected function getPackageProviders($app)
    {
        return [
            QueueMonitorProvider::class,
        ];
    }
}
