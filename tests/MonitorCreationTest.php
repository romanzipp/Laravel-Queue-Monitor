<?php

namespace romanzipp\QueueMonitor\Tests;

use romanzipp\QueueMonitor\Models\Monitor;
use romanzipp\QueueMonitor\Tests\Support\ExtendingMonitoredJob;
use romanzipp\QueueMonitor\Tests\Support\MonitoredJob;
use romanzipp\QueueMonitor\Tests\Support\NotMonitoredJob;

class MonitorCreationTest extends TestCase
{
    public function testCreateMonitor()
    {
        $this->dispatchNow(new MonitoredJob);

        $this->assertCount(1, Monitor::all());
        $this->assertEquals(MonitoredJob::class, Monitor::query()->first()->name);
    }

    public function testCreateMonitorFromExtending()
    {
        $this->dispatchNow(new ExtendingMonitoredJob);

        $this->assertCount(1, Monitor::all());
        $this->assertEquals(ExtendingMonitoredJob::class, Monitor::query()->first()->name);
    }

    public function testDontCreateMonitor()
    {
        $this->dispatchNow(new NotMonitoredJob);

        $this->assertCount(0, Monitor::all());
    }
}
