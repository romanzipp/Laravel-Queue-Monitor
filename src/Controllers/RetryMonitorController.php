<?php

namespace romanzipp\QueueMonitor\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use romanzipp\QueueMonitor\Enums\MonitorStatus;
use romanzipp\QueueMonitor\Models\Monitor;
use romanzipp\QueueMonitor\Services\QueueMonitor;

class RetryMonitorController
{
    public function __invoke(Request $request, int $monitorId): RedirectResponse
    {
        /** @var \romanzipp\QueueMonitor\Models\Monitor $monitor */
        $monitor = QueueMonitor::getModel()
            ->query()
            ->where('status', MonitorStatus::FAILED)
            ->where('retried', false)
            ->whereNotNull('job_uuid')
            ->findOrFail($monitorId);

        if (is_a($monitor, Monitor::class)) {
            $monitor->retry();
        }

        return redirect()->route('queue-monitor::index');
    }
}
