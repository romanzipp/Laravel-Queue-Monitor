<?php

namespace romanzipp\QueueMonitor\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use romanzipp\QueueMonitor\Enums\MonitorStatus;
use romanzipp\QueueMonitor\Models\Monitor;

class RetryMonitorController
{
    public function __invoke(Request $request, int $monitorId): RedirectResponse
    {
        /** @var \romanzipp\QueueMonitor\Models\Monitor $monitor */
        $monitor = Monitor::query()
            ->where('status', MonitorStatus::FAILED)
            ->where('retried', false)
            ->whereNotNull('job_uuid')
            ->find($monitorId) ?? throw new ModelNotFoundException();

        if (is_a($monitor, Monitor::class)) {
            $monitor->retried = true;
            $monitor->save();

            Artisan::call('queue:retry', ['id' => $monitor->job_uuid]);
        }

        return redirect()->route('queue-monitor::index');
    }
}
