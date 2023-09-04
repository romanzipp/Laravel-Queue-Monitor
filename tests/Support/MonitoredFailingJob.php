<?php

namespace romanzipp\QueueMonitor\Tests\Support;

use romanzipp\QueueMonitor\Traits\IsMonitored;

class MonitoredFailingJob extends BaseJob
{
    use IsMonitored;

    public static int $count = 0;

    public function handle(): void
    {
        ++self::$count;

        if (1 === self::$count) {
            throw new IntentionallyFailedException('Whoops');
        }
    }
}
