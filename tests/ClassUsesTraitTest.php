<?php

namespace romanzipp\QueueMonitor\Tests;

use romanzipp\QueueMonitor\Services\ClassUses;
use romanzipp\QueueMonitor\Tests\Support\MonitoredExtendingJob;
use romanzipp\QueueMonitor\Tests\Support\MonitoredJob;
use romanzipp\QueueMonitor\Tests\TestCases\DatabaseTestCase;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class ClassUsesTraitTest extends DatabaseTestCase
{
    public function testUsingMonitorTrait()
    {
        $this->assertArrayHasKey(
            IsMonitored::class,
            ClassUses::classUsesRecursive(MonitoredJob::class)
        );
    }

    public function testUsingMonitorTraitExtended()
    {
        $this->assertArrayHasKey(
            IsMonitored::class,
            ClassUses::classUsesRecursive(MonitoredExtendingJob::class)
        );
    }
}
