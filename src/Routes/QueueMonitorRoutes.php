<?php

namespace romanzipp\QueueMonitor\Routes;

use Closure;

class QueueMonitorRoutes
{
    /**
     * Scaffold the Queue Monitor UI routes.
     *
     * @return \Closure
     */
    public function queueMonitor(): Closure
    {
        return function (array $options = []) {
            /** @var \Illuminate\Routing\Router $this */
            $this->get('', '\romanzipp\QueueMonitor\Controllers\ShowQueueMonitorController')->name('queue-monitor::index');

            if (config('queue-monitor.ui.allow_deletion')) {
                $this->delete('monitors/{monitor}', '\romanzipp\QueueMonitor\Controllers\DeleteMonitorController')->name('queue-monitor::destroy');
            }

            if (config('queue-monitor.ui.allow_purge')) {
                $this->delete('purge', '\romanzipp\QueueMonitor\Controllers\PurgeMonitorsController')->name('queue-monitor::purge');
            }
        };
    }
}
