<?php

namespace romanzipp\QueueMonitor\Tests;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use romanzipp\QueueMonitor\Models\Monitor;
use romanzipp\QueueMonitor\Providers\QueueMonitorProvider;
use romanzipp\QueueMonitor\Tests\Support\MonitoredFailingJob;
use romanzipp\QueueMonitor\Tests\Support\MonitoredJob;
use romanzipp\QueueMonitor\Tests\TestCases\DatabaseTestCase;

class MonitorRetryTest extends DatabaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        config([
            'queue-monitor.ui.enabled' => true,
            'queue-monitor.ui.allow_retry' => true,
        ]);
    }

    protected function defineRoutes($router)
    {
        $router->group(QueueMonitorProvider::buildRouteGroupConfig(config()), function () use (&$router) {
            require __DIR__ . '/../routes/queue-monitor.php';
        });
    }

    protected function tearDown(): void
    {
        MonitoredFailingJob::$count = 0;

        Monitor::query()->each(fn (Monitor $monitor) => $monitor->delete());

        parent::tearDown();
    }

    public function testRetryFailedMonitor(): void
    {
        $this
            ->dispatch(new MonitoredFailingJob())
            ->assertDispatched(MonitoredFailingJob::class)
            ->workQueue();

        self::assertEquals(0, Monitor::query()->first()->retried);
        self::assertEquals(1, Monitor::query()->count());

        $this
            ->patch(route('queue-monitor::retry', ['monitor' => Monitor::query()->first()->id]))
            ->assertStatus(302);

        $this->workQueue();

        // pgsql fails here
        self::assertEquals(1, Monitor::query()->first()->retried);
        self::assertEquals(2, Monitor::query()->count());
    }

    public function testDontRetryMonitorWhenAlreadyRetried(): void
    {
        $this
            ->dispatch(new MonitoredFailingJob())
            ->assertDispatched(MonitoredFailingJob::class)
            ->workQueue();

        $this
            ->patch(route('queue-monitor::retry', ['monitor' => Monitor::query()->first()->id]))
            ->assertStatus(302);

        $this->workQueue();

        $this->expectException(ModelNotFoundException::class);

        $this
            ->patch(route('queue-monitor::retry', ['monitor' => Monitor::query()->first()->id]))
            ->assertStatus(404);
    }

    public function testDontRetrySucceededMonitor(): void
    {
        $this
            ->dispatch(new MonitoredJob())
            ->assertDispatched(MonitoredJob::class)
            ->workQueue();

        $this->expectException(ModelNotFoundException::class);
        $this->patch(route('queue-monitor::retry', ['monitor' => Monitor::query()->first()->id]));
    }
}
