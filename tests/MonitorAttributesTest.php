<?php

namespace romanzipp\QueueMonitor\Tests;

use romanzipp\QueueMonitor\Models\Monitor;
use romanzipp\QueueMonitor\Tests\Support\MonitoredJobWithData;
use romanzipp\QueueMonitor\Tests\Support\MonitoredJobWithMergedData;
use romanzipp\QueueMonitor\Tests\Support\MonitoredJobWithMergedDataConflicting;
use romanzipp\QueueMonitor\Tests\Support\MonitoredJobWithProgress;

class MonitorAttributesTest extends TestCase
{
    public function testData()
    {
        $this->dispatch(new MonitoredJobWithData());
        $this->workQueue();

        $this->assertInstanceOf(Monitor::class, $monitor = Monitor::query()->first());
        $this->assertEquals(MonitoredJobWithData::class, $monitor->name);
        $this->assertEquals('{"foo":"bar"}', $monitor->data);
        $this->assertEquals(['foo' => 'bar'], $monitor->getData());
    }

    public function testMergeData()
    {
        $this->dispatch(new MonitoredJobWithMergedData());
        $this->workQueue();

        $this->assertInstanceOf(Monitor::class, $monitor = Monitor::query()->first());
        $this->assertEquals(MonitoredJobWithMergedData::class, $monitor->name);
        $this->assertEquals('{"foo":"foo","bar":"bar"}', $monitor->data);
        $this->assertEquals(['foo' => 'foo', 'bar' => 'bar'], $monitor->getData());
    }

    public function testMergeDataConflicting()
    {
        $this->dispatch(new MonitoredJobWithMergedDataConflicting());
        $this->workQueue();

        $this->assertInstanceOf(Monitor::class, $monitor = Monitor::query()->first());
        $this->assertEquals(MonitoredJobWithMergedDataConflicting::class, $monitor->name);
        $this->assertEquals('{"foo":"new"}', $monitor->data);
        $this->assertEquals(['foo' => 'new'], $monitor->getData());
    }

    public function testProgress()
    {
        $this->dispatch(new MonitoredJobWithProgress(50));
        $this->workQueue();

        $this->assertInstanceOf(Monitor::class, $monitor = Monitor::query()->first());
        $this->assertEquals(MonitoredJobWithProgress::class, $monitor->name);
        $this->assertEquals(50, $monitor->progress);
    }

    public function testProgressTooLarge()
    {
        $this->dispatch(new MonitoredJobWithProgress(120));
        $this->workQueue();

        $this->assertInstanceOf(Monitor::class, $monitor = Monitor::query()->first());
        $this->assertEquals(MonitoredJobWithProgress::class, $monitor->name);
        $this->assertEquals(100, $monitor->progress);
    }

    public function testProgressNegative()
    {
        $this->dispatch(new MonitoredJobWithProgress(-20));
        $this->workQueue();

        $this->assertInstanceOf(Monitor::class, $monitor = Monitor::query()->first());
        $this->assertEquals(MonitoredJobWithProgress::class, $monitor->name);
        $this->assertEquals(0, $monitor->progress);
    }
}
