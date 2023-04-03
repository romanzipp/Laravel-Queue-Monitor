<?php

namespace romanzipp\QueueMonitor\Controllers;

use Carbon\Carbon;
use Illuminate\Database\SqlServerConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use romanzipp\QueueMonitor\Controllers\Payloads\Metric;
use romanzipp\QueueMonitor\Controllers\Payloads\Metrics;
use romanzipp\QueueMonitor\Enums\MonitorStatus;
use romanzipp\QueueMonitor\Models\Contracts\MonitorContract;
use romanzipp\QueueMonitor\Services\QueueMonitor;

class ShowQueueMonitorController
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'status' => ['nullable', 'numeric', Rule::in(MonitorStatus::toArray())],
            'queue' => ['nullable', 'string'],
            'name' => ['nullable', 'string'],
        ]);

        $filters = [
            'status' => isset($data['status']) ? (int) $data['status'] : null,
            'queue' => $data['queue'] ?? 'all',
            'name' => $data['name'] ?? null,
        ];

        $jobsQuery = QueueMonitor::getModel()->newQuery();

        if (null !== $filters['status']) {
            $jobsQuery->where('status', $data['status']);
        }

        if ('all' !== $filters['queue']) {
            $jobsQuery->where('queue', $filters);
        }

        if (null !== $filters['name']) {
            $jobsQuery->where('name', 'like', "%{$filters['name']}%");
        }

        $jobsQuery
            ->orderBy('started_at', 'desc')
            ->orderBy('started_at_exact', 'desc');

        $jobs = $jobsQuery
            ->paginate(config('queue-monitor.ui.per_page'))
            ->appends(
                $request->all()
            );

        $queues = QueueMonitor::getModel()
            ->newQuery()
            ->select('queue')
            ->groupBy('queue')
            ->get()
            ->map(function (MonitorContract $monitor) {
                return $monitor->queue;
            })
            ->toArray();

        $metrics = null;

        if (config('queue-monitor.ui.show_metrics')) {
            $metrics = $this->collectMetrics();
        }

        return view('queue-monitor::jobs', [
            'jobs' => $jobs,
            'filters' => $filters,
            'queues' => $queues,
            'metrics' => $metrics,
            'statuses' => MonitorStatus::toNamedArray(),
        ]);
    }

    public function collectMetrics(): Metrics
    {
        $timeFrame = config('queue-monitor.ui.metrics_time_frame') ?? 2;

        $metrics = new Metrics();

        $sqlTimestampDiffFunction = 'TIMESTAMPDIFF';
        $connection = DB::connection();

        if ($connection instanceof SqlServerConnection) {
            $sqlTimestampDiffFunction = 'DATEDIFF';
        }

        $aggregationColumns = [
            DB::raw('COUNT(*) as count'),
            DB::raw("SUM({$sqlTimestampDiffFunction}(SECOND, `started_at`, `finished_at`)) as `total_time_elapsed`"),
            DB::raw("AVG({$sqlTimestampDiffFunction}(SECOND, `started_at`, `finished_at`)) as `average_time_elapsed`"),
        ];

        $aggregatedInfo = QueueMonitor::getModel()
            ->newQuery()
            ->select($aggregationColumns)
            ->where('status', '!=', MonitorStatus::RUNNING)
            ->where('started_at', '>=', Carbon::now()->subDays($timeFrame))
            ->first();

        $aggregatedComparisonInfo = QueueMonitor::getModel()
            ->newQuery()
            ->select($aggregationColumns)
            ->where('status', '!=', MonitorStatus::RUNNING)
            ->where('started_at', '>=', Carbon::now()->subDays($timeFrame * 2))
            ->where('started_at', '<=', Carbon::now()->subDays($timeFrame))
            ->first();

        if (null === $aggregatedInfo || null === $aggregatedComparisonInfo) {
            return $metrics;
        }

        return $metrics
            ->push(
                new Metric('Total Jobs Executed', $aggregatedInfo->count ?? 0, $aggregatedComparisonInfo->count, '%d')
            )
            ->push(
                new Metric('Total Execution Time', $aggregatedInfo->total_time_elapsed ?? 0, $aggregatedComparisonInfo->total_time_elapsed, '%ds')
            )
            ->push(
                new Metric('Average Execution Time', $aggregatedInfo->average_time_elapsed ?? 0, $aggregatedComparisonInfo->average_time_elapsed, '%0.2fs')
            );
    }
}
