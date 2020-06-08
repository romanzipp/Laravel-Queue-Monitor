<?php

namespace romanzipp\QueueMonitor\Tests\Support;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MonitoredBroadcastingJob extends MonitoredJob implements ShouldBroadcast
{
    public function broadcastOn()
    {
        return new Channel('test');
    }
}
