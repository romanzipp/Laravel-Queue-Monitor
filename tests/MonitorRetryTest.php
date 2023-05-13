<?php

namespace romanzipp\QueueMonitor\Tests;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use romanzipp\QueueMonitor\Models\Monitor;
use romanzipp\QueueMonitor\Tests\Support\MonitoredFailingJob;
use romanzipp\QueueMonitor\Tests\Support\MonitoredJob;
use romanzipp\QueueMonitor\Tests\TestCases\DatabaseTestCase;

class MonitorRetryTest extends DatabaseTestCase
{
    public function setUp(): void
    {
        parent::setup();

        config([
            'queue-monitor.ui.allow_retry' => true,
        ]);
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('queue-monitor.ui.enabled', true);
    }

    protected function tearDown(): void
    {
        Monitor::query()->truncate();

        parent::tearDown();
    }

    public function testRetryFailedMonitor(): void
    {
        $this
            ->dispatch(new MonitoredFailingJob())
            ->assertDispatched(MonitoredFailingJob::class)
            ->workQueue();

        self::assertEquals(0, Monitor::first()->retried);
        self::assertEquals(1, Monitor::count());

        $this->patch(route('queue-monitor::retry', ['monitor' => Monitor::first()]));
        $this->workQueue();

        self::assertEquals(1, Monitor::first()->retried);
        self::assertEquals(2, Monitor::count());
    }

    public function testDontRetryMonitorWhenAllreadyRetried(): void
    {
        $this
            ->dispatch(new MonitoredFailingJob())
            ->assertDispatched(MonitoredFailingJob::class)
            ->workQueue();

        $this->patch(route('queue-monitor::retry', ['monitor' => Monitor::first()]));
        $this->workQueue();

        $this->expectException(ModelNotFoundException::class);
        $this->patch(route('queue-monitor::retry', ['monitor' => Monitor::first()]));
    }

    public function testDontRetrySucceededMonitor(): void
    {
        $this
            ->dispatch(new MonitoredJob())
            ->assertDispatched(MonitoredJob::class)
            ->workQueue();

        $this->expectException(ModelNotFoundException::class);
        $this->patch(route('queue-monitor::retry', ['monitor' => Monitor::first()]));
    }
}
