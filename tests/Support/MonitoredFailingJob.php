<?php

namespace romanzipp\QueueMonitor\Tests\Support;

use romanzipp\QueueMonitor\Traits\IsMonitored;

class MonitoredFailingJob extends BaseJob
{
    use IsMonitored;

    public function handle(): void
    {
        $this->queueData(['foo'=>'bar']);
        throw new IntentionallyFailedException('Whoops');
    }
}
