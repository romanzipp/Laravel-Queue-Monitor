<?php

namespace romanzipp\QueueMonitor\Tests;

use romanzipp\QueueMonitor\Models\Monitor;
use romanzipp\QueueMonitor\Tests\Support\FailingJob;

class MonitorStateHandlingTest extends TestCase
{
    public function testCreateMonitor()
    {
        rescue(function () {
            $this->dispatch(new FailingJob);
        });

        $this->assertCount(1, Monitor::all());
        $this->assertInstanceOf(Monitor::class, $monitor = Monitor::query()->first());
        $this->assertEquals(FailingJob::class, $monitor->name);
    }
}
