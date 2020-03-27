<?php

namespace romanzipp\QueueMonitor\Tests;

use romanzipp\QueueMonitor\Models\Monitor;
use romanzipp\QueueMonitor\Tests\Support\MonitoredJobWithData;

class MonitorAttributesTest extends TestCase
{
    public function testSettingQueueData()
    {
        $this->dispatch(new MonitoredJobWithData);

        $this->assertInstanceOf(Monitor::class, $monitor = Monitor::query()->first());
        $this->assertEquals(MonitoredJobWithData::class, $monitor->name);
        $this->assertEquals('{"foo":"bar"}', $monitor->data);
        $this->assertEquals(['foo' => 'bar'], $monitor->getData());
    }
}
