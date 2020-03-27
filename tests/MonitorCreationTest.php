<?php

namespace romanzipp\QueueMonitor\Tests;

use romanzipp\QueueMonitor\Models\Monitor;
use romanzipp\QueueMonitor\Tests\Support\ExtendingJob;
use romanzipp\QueueMonitor\Tests\Support\Job;
use romanzipp\QueueMonitor\Tests\Support\NotMonitoredJob;

class MonitorCreationTest extends TestCase
{
    public function testCreateMonitor()
    {
        $this->dispatch(new Job);

        $this->assertInstanceOf(Monitor::class, $monitor = Monitor::query()->first());
        $this->assertEquals(Job::class, $monitor->name);
    }

    public function testCreateMonitorFromExtending()
    {
        $this->dispatch(new ExtendingJob);

        $this->assertInstanceOf(Monitor::class, $monitor = Monitor::query()->first());
        $this->assertEquals(ExtendingJob::class, $monitor->name);
    }

    public function testDontCreateMonitor()
    {
        $this->dispatch(new NotMonitoredJob);

        $this->assertCount(0, Monitor::all());
    }
}
