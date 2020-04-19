<?php

namespace romanzipp\QueueMonitor\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use romanzipp\QueueMonitor\Models\Monitor;

class ShowQueueMonitorController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'only_failed' => ['nullable'],
        ]);

        $jobs = Monitor::query()
            ->when(Arr::get($data, 'only_failed'), static function (Builder $builder) {
                $builder->whereNotNull('failed_at');
            })
            ->paginate(
                config('queue-monitor.ui.per_page')
            )
            ->appends(
                $request->all()
            );

        return view('queue-monitor::jobs', [
            'jobs' => $jobs,
        ]);
    }
}
