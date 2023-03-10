<?php

namespace romanzipp\QueueMonitor\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use romanzipp\QueueMonitor\Console\Commands\Concerns\HandlesDateInputs;
use romanzipp\QueueMonitor\Enums\MonitorStatus;
use romanzipp\QueueMonitor\Services\QueueMonitor;

class MarkJobsAsStaleCommand extends Command
{
    use HandlesDateInputs;

    protected $signature = 'queue-monitor:stale {--before=} {--beforeDays=} {--beforeInterval=} {--dry}';

    public function handle(): int
    {
        $beforeDate = self::parseBeforeDate($this);
        if (null === $beforeDate) {
            $this->error('Needs at least --before or --beforeDays arguments');

            return 1;
        }

        $query = QueueMonitor::getModel()
            ->newQuery()
            ->where('status', MonitorStatus::RUNNING)
            ->where('started_at', '<', $beforeDate);

        $this->info(
            sprintf('Marking %d jobs after %s as stale', $count = $query->count(), $beforeDate->format('Y-m-d H:i:s'))
        );

        $query->chunk(500, function (Collection $models, int $page) use ($count) {
            $this->info(
                sprintf('Deleted chunk %d / %d', $page, abs($count / 200))
            );

            if ($this->option('dry')) {
                return;
            }

            DB::table(QueueMonitor::getModel()->getTable())
                ->whereIn('id', $models->pluck('id'))
                ->update([
                    'status' => MonitorStatus::STALE,
                ]);
        });

        return 0;
    }
}
