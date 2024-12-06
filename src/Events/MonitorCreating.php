<?php

namespace romanzipp\QueueMonitor\Events;


use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use romanzipp\QueueMonitor\Models\Monitor;

class MonitorCreating
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Monitor $monitor;

    /**
     * Create a new event instance.
     */
    public function __construct(Monitor $monitor) {
        $this->monitor = $monitor;
    }
}
