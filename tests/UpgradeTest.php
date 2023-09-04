<?php

namespace romanzipp\QueueMonitor\Tests;

use Carbon\Carbon;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use romanzipp\QueueMonitor\Enums\MonitorStatus;
use romanzipp\QueueMonitor\Models\Monitor;
use romanzipp\QueueMonitor\Tests\TestCases\TestCase;

class UpgradeTest extends TestCase
{
    use DatabaseMigrations;

    public function testUpgrade()
    {
        if ($this->app['db']->connection() instanceof SQLiteConnection) {
            self::markTestSkipped('Not suppported in SQLite');
        }

        $migration = new \UpdateQueueMonitorTable();
        $migration->down();

        $running = Monitor::query()->create([
            'job_id' => 'foo',
        ]);

        $succeeded = Monitor::query()->create([
            'job_id' => 'foo',
            'failed' => false,
            'finished_at' => Carbon::now(),
            'finished_at_exact' => Carbon::now(),
        ]);

        $failed = Monitor::query()->create([
            'job_id' => 'foo',
            'failed' => true,
            'finished_at' => Carbon::now(),
            'finished_at_exact' => Carbon::now(),
        ]);

        $migration->up();

        $running->refresh();
        $succeeded->refresh();
        $failed->refresh();

        self::assertSame(MonitorStatus::RUNNING, $running->status);
        self::assertSame(MonitorStatus::SUCCEEDED, $succeeded->status);
        self::assertSame(MonitorStatus::FAILED, $failed->status);
    }
}
