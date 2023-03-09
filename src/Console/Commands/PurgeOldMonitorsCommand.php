<?php

namespace romanzipp\QueueMonitor\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use romanzipp\QueueMonitor\Enums\MonitorStatus;
use romanzipp\QueueMonitor\Services\QueueMonitor;

class PurgeOldMonitorsCommand extends Command
{
    protected $signature = 'queue-monitor:purge {--before=} {--force} {--only-succeeded} {--queue=}';

    public function handle(): int
    {
        $beforeDate = Carbon::parse($this->option('before'));

        $query = QueueMonitor::getModel()
            ->newQuery()
            ->where('started_at', '>', $beforeDate);

        $queues = explode(',', $this->argument('queue') ?? '');

        /** @phpstan-ignore-next-line */
        if (count($queues) > 0) {
            $query->whereIn('queue', array_map('trim', $queues));
        }

        if ($this->option('only-succeeded')) {
            $query->where('status', '=', MonitorStatus::SUCCEEDED);
        }

        $count = $query->count();

        if ( ! $this->argument('force')) {
            $ok = $this->confirm(
                sprintf('Purging %d jobs before %s. Continue?', $count, $beforeDate->format('Y-m-d H:i:s'))
            );

            if ( ! $ok) {
                return 0;
            }
        }

        $query->chunk(200, function (Collection $models, int $page) use ($count) {
            $this->info(
                sprintf('Deleted chunk %d / %d', $page, abs($count / 200))
            );

            DB::table(QueueMonitor::getModel()->getTable())
                ->whereIn('id', $models->pluck('id'))
                ->delete();
        });

        return 1;
    }
}
