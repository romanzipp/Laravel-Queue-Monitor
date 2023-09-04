<?php

namespace romanzipp\QueueMonitor\Tests;

use romanzipp\QueueMonitor\Models\Monitor;
use romanzipp\QueueMonitor\Tests\Support\MonitoredBroadcastingJob;
use romanzipp\QueueMonitor\Tests\Support\MonitoredExtendingJob;
use romanzipp\QueueMonitor\Tests\Support\MonitoredJob;
use romanzipp\QueueMonitor\Tests\Support\MonitoredJobWithArguments;
use romanzipp\QueueMonitor\Tests\Support\MonitoredPartiallyKeptFailingJob;
use romanzipp\QueueMonitor\Tests\Support\MonitoredPartiallyKeptJob;
use romanzipp\QueueMonitor\Tests\Support\UnmonitoredJob;
use romanzipp\QueueMonitor\Tests\TestCases\DatabaseTestCase;

class MonitorCreationTest extends DatabaseTestCase
{
    public function testCreateMonitor()
    {
        $this
            ->dispatch(new MonitoredJob())
            ->assertDispatched(MonitoredJob::class)
            ->workQueue();

        self::assertSame(1, Monitor::query()->count());
        self::assertInstanceOf(Monitor::class, $monitor = Monitor::query()->first());
        self::assertEquals(MonitoredJob::class, $monitor->name);
    }

    public function testCreateMonitorFromExtending()
    {
        $this
            ->dispatch(new MonitoredExtendingJob())
            ->assertDispatched(MonitoredExtendingJob::class)
            ->workQueue();

        self::assertSame(1, Monitor::query()->count());
        self::assertInstanceOf(Monitor::class, $monitor = Monitor::query()->first());
        self::assertEquals(MonitoredExtendingJob::class, $monitor->name);
    }

    public function testDontCreateMonitor()
    {
        $this
            ->dispatch(new UnmonitoredJob())
            ->assertDispatched(UnmonitoredJob::class)
            ->workQueue();

        self::assertSame(0, Monitor::query()->count());
    }

    public function testDontKeepSuccessfulMonitor()
    {
        $this
            ->dispatch(new MonitoredPartiallyKeptJob())
            ->assertDispatched(MonitoredPartiallyKeptJob::class)
            ->workQueue();

        self::assertSame(0, Monitor::query()->count());
    }

    public function testDontKeepSuccessfulMonitorFailing()
    {
        $this
            ->dispatch(new MonitoredPartiallyKeptFailingJob())
            ->assertDispatched(MonitoredPartiallyKeptFailingJob::class)
            ->workQueue();

        self::assertSame(1, Monitor::query()->count());
        self::assertInstanceOf(Monitor::class, $monitor = Monitor::query()->first());
        self::assertEquals(MonitoredPartiallyKeptFailingJob::class, $monitor->name);
    }

    public function testBroadcastingJob()
    {
        $this
            ->dispatch(new MonitoredBroadcastingJob())
            ->assertDispatched(MonitoredBroadcastingJob::class)
            ->workQueue();

        self::assertSame(1, Monitor::query()->count());
        self::assertInstanceOf(Monitor::class, $monitor = Monitor::query()->first());
        self::assertEquals(MonitoredBroadcastingJob::class, $monitor->name);
    }

    public function testDispatchingJobViaDispatchableTrait()
    {
        MonitoredJob::dispatch();

        $this->assertDispatched(MonitoredJob::class);
        $this->workQueue();

        self::assertSame(1, Monitor::query()->count());
        self::assertInstanceOf(Monitor::class, $monitor = Monitor::query()->first());
        self::assertEquals(MonitoredJob::class, $monitor->name);
    }

    public function testDispatchingJobViaDispatchableTraitWithArguments()
    {
        MonitoredJobWithArguments::dispatch('foo');

        $this->assertDispatched(MonitoredJobWithArguments::class);
        $this->workQueue();

        self::assertSame(1, Monitor::query()->count());
        self::assertInstanceOf(Monitor::class, $monitor = Monitor::query()->first());
        self::assertEquals(MonitoredJobWithArguments::class, $monitor->name);
    }
}
