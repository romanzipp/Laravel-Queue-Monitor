<?php

namespace romanzipp\QueueMonitor\Tests\Support;

use romanzipp\QueueMonitor\Traits\IsMonitored;

class MonitoredPartiallyKeptFailingJob extends BaseJob
{
    use IsMonitored;

    public static function keepMonitorOnSuccess(): bool
    {
        return false;
    }

    public function handle(): void
    {
        throw new IntentionallyFailedException('Whoops');
    }
}
