<?php

namespace romanzipp\QueueMonitor\Routes;

use romanzipp\QueueMonitor\Controllers;

class QueueMonitorRoutes
{
    /**
     * Scaffold the Queue Monitor UI routes.
     *
     * @return \Closure
     */
    public function queueMonitor(): \Closure
    {
        return function (array $options = []) {
            /** @var \Illuminate\Routing\Router $this */
            $this->get('', Controllers\ShowQueueMonitorController::class)->name('queue-monitor::index');

            if (config('queue-monitor.ui.allow_deletion')) {
                $this->delete('monitors/{monitor}', Controllers\DeleteMonitorController::class)->name('queue-monitor::destroy');
            }

            if (config('queue-monitor.ui.allow_purge')) {
                $this->delete('purge', Controllers\PurgeMonitorsController::class)->name('queue-monitor::purge');
            }
        };
    }
}
