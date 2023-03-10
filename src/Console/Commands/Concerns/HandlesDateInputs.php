<?php

namespace romanzipp\QueueMonitor\Console\Commands\Concerns;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Console\Command;

trait HandlesDateInputs
{
    public static function parseBeforeDate(Command $command): ?Carbon
    {
        if ($before = $command->option('before')) {
            return Carbon::parse($before);
        }
        if ($beforeDays = $command->option('beforeDays')) {
            return Carbon::now()->subDays((int) $beforeDays);
        }

        if ($interval = $command->option('beforeInterval')) {
            return Carbon::now()->sub(
                new CarbonInterval($interval)
            );
        }

        return null;
    }
}
