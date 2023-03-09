<?php

namespace romanzipp\QueueMonitor\Tests\Support;

use romanzipp\QueueMonitor\Providers\QueueMonitorProvider;

class TestQueueMonitorProvider extends QueueMonitorProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom([
            __DIR__ . '/../../migrations',
        ]);

        parent::boot();
    }
}
