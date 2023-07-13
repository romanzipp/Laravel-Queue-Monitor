<?php

namespace romanzipp\QueueMonitor\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use romanzipp\QueueMonitor\Services\QueueMonitor;

class DeleteMonitorController
{
    public function __invoke(Request $request, $monitorId): RedirectResponse
    {
        // find model here to use QueueMonitor model and connection
        QueueMonitor::getModel()->find($monitorId)->delete();

        return redirect()->route('queue-monitor::index');
    }
}
