<?php

namespace romanzipp\QueueMonitor\Tests;

use romanzipp\QueueMonitor\Models\Monitor;
use romanzipp\QueueMonitor\Tests\Support\IntentionallyFailedException;
use romanzipp\QueueMonitor\Tests\Support\MonitoredFailingJob;
use romanzipp\QueueMonitor\Tests\Support\MonitoredFailingJobWithHugeExceptionMessage;

class MonitorStateHandlingTest extends TestCase
{
    public function testFailing()
    {
        self::markTestSkipped('Investigate');

        $this->dispatch(new MonitoredFailingJob());
        $this->workQueue();

        self::assertCount(1, Monitor::all());
        self::assertInstanceOf(Monitor::class, $monitor = Monitor::query()->first());
        self::assertEquals(MonitoredFailingJob::class, $monitor->name);

        self::assertEquals(IntentionallyFailedException::class, $monitor->exception_class);
        self::assertEquals('Whoops', $monitor->exception_message);
        self::assertInstanceOf(IntentionallyFailedException::class, $monitor->getException());
    }

    public function testFailingWithHugeExceptionMessage()
    {
        self::markTestSkipped('Investigate');

        $this->dispatch(new MonitoredFailingJobWithHugeExceptionMessage());
        $this->workQueue();

        self::assertCount(1, Monitor::all());
        self::assertInstanceOf(Monitor::class, $monitor = Monitor::query()->first());
        self::assertEquals(MonitoredFailingJobWithHugeExceptionMessage::class, $monitor->name);
        self::assertEquals(IntentionallyFailedException::class, $monitor->exception_class);
        self::assertEquals(str_repeat('x', config('queue-monitor.db_max_length_exception_message')), $monitor->exception_message);
        self::assertInstanceOf(IntentionallyFailedException::class, $monitor->getException());
    }
}
