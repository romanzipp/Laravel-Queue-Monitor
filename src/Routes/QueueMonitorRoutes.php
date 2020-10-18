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

            /* @var \Illuminate\Routing\Router $this */

            $this->get('', '\romanzipp\QueueMonitor\Controllers\ShowQueueMonitorController');
        };
    }
}
