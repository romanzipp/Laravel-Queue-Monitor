<?php

namespace romanzipp\QueueMonitor\Controllers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use romanzipp\QueueMonitor\Controllers\Payloads\Metric;
use romanzipp\QueueMonitor\Controllers\Payloads\Metrics;
use romanzipp\QueueMonitor\Services\QueueMonitor;

class ShowQueueMonitorController
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'only_failed' => ['nullable'],
        ]);

        $filters = [
            'onlyFailed' => (bool) Arr::get($data, 'only_failed'),
        ];

        $jobs = QueueMonitor::getModel()
            ->newQuery()
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

        $metrics = null;

        if (config('queue-monitor.ui.show_metrics')) {
            $metrics = $this->collectMetrics();
        }

        return view('queue-monitor::jobs', [
            'jobs' => $jobs,
            'filters' => $filters,
            'metrics' => $metrics,
        ]);
    }

    public function collectMetrics(): Metrics
    {
        $timeFrame = 30; // days

        $metrics = new Metrics();

        $aggregationColumns = [
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(time_elapsed) as total_time_elapsed'),
            DB::raw('AVG(time_elapsed) as average_time_elapsed'),
        ];

        $aggregatedInfo = QueueMonitor::getModel()
            ->newQuery()
            ->select($aggregationColumns)
            ->where('started_at', '>=', Carbon::now()->subDays($timeFrame))
            ->first();

        $aggregatedComparisonInfo = QueueMonitor::getModel()
            ->newQuery()
            ->select($aggregationColumns)
            ->where('started_at', '>=', Carbon::now()->subDays($timeFrame * 2))
            ->where('started_at', '<=', Carbon::now()->subDays($timeFrame))
            ->first();

        if (null === $aggregatedInfo || null === $aggregatedComparisonInfo) {
            return $metrics;
        }

        return $metrics
            ->push(
                new Metric('Total Jobs Executed', $aggregatedInfo->count, $aggregatedComparisonInfo->count, '%d')
            )
            ->push(
                new Metric('Total Execution Time', $aggregatedInfo->total_time_elapsed, $aggregatedComparisonInfo->total_time_elapsed, '%ds')
            )
            ->push(
                new Metric('Average Execution Time', $aggregatedInfo->average_time_elapsed, $aggregatedComparisonInfo->average_time_elapsed, '%0.2fs')
            );
    }
}
