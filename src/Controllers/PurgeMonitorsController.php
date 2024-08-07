<?php

namespace romanzipp\QueueMonitor\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use romanzipp\QueueMonitor\Models\Contracts\MonitorContract;
use romanzipp\QueueMonitor\Services\QueueMonitor;

class PurgeMonitorsController
{
    public function __invoke(Request $request): RedirectResponse
    {
        $model = QueueMonitor::getModel();

        $model->newQuery()->delete();

        return redirect()->route('queue-monitor::index');
    }
}
