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

        $filters = [
            'onlyFailed' => (bool) Arr::get($data, 'only_failed'),
        ];

        $jobs = Monitor::query()
            ->when($filters['onlyFailed'], static function (Builder $builder) {
                $builder->where('failed', 1);
            })
            ->ordered()
            ->paginate(
                config('queue-monitor.ui.per_page')
            )
            ->appends(
                $request->all()
            );

        return view('queue-monitor::jobs', [
            'jobs' => $jobs,
            'filters' => $filters,
        ]);
    }
}
