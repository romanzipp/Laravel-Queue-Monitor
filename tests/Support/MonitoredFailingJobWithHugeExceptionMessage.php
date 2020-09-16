<?php

namespace romanzipp\QueueMonitor\Tests\Support;

use romanzipp\QueueMonitor\Services\QueueMonitor;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class MonitoredFailingJobWithHugeExceptionMessage extends BaseJob
{
    use IsMonitored;

    public function handle(): void
    {
        throw new IntentionallyFailedException(
            str_repeat('x', QueueMonitor::MAX_BYTES_TEXT + 10)
        );
    }
}
