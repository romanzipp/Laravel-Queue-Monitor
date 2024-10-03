<?php

namespace romanzipp\QueueMonitor\Models\Contracts;

/**
 * @mixin \romanzipp\QueueMonitor\Models\Monitor
 */
interface MonitorContract
{
    /**
     * @return \Illuminate\Database\Eloquent\Builder<\romanzipp\QueueMonitor\Models\Monitor>
     */
    public function newQuery();

    /**
     * @return string
     */
    public function getTable();
}
