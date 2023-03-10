<?php

namespace romanzipp\QueueMonitor\Tests;

use Carbon\Carbon;
use romanzipp\QueueMonitor\Enums\MonitorStatus;
use romanzipp\QueueMonitor\Models\Monitor;
use romanzipp\QueueMonitor\Tests\TestCases\DatabaseTestCase;

class StaleCommandTest extends DatabaseTestCase
{
    protected function tearDown(): void
    {
        Monitor::query()->truncate();

        parent::tearDown();
    }

    public function testStale()
    {
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::SUCCEEDED, 'started_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::RUNNING,   'started_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::FAILED,    'started_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::STALE,     'started_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::SUCCEEDED, 'started_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::RUNNING,   'started_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::FAILED,    'started_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::STALE,     'started_at' => Carbon::now()->subDays(15)]);

        self::assertSame(2, Monitor::query()->where('status', MonitorStatus::STALE)->count());

        $this->artisan('queue-monitor:stale --before=' . Carbon::now()->subDays(30)->format('Y-m-d'));

        self::assertSame(3, Monitor::query()->where('status', MonitorStatus::STALE)->count());
    }

    public function testStaleDays()
    {
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::SUCCEEDED, 'started_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::RUNNING,   'started_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::FAILED,    'started_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::STALE,     'started_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::SUCCEEDED, 'started_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::RUNNING,   'started_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::FAILED,    'started_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::STALE,     'started_at' => Carbon::now()->subDays(15)]);

        self::assertSame(2, Monitor::query()->where('status', MonitorStatus::STALE)->count());

        $this->artisan('queue-monitor:stale --beforeDays=30');

        self::assertSame(3, Monitor::query()->where('status', MonitorStatus::STALE)->count());
    }

    public function testStaleInterval()
    {
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::SUCCEEDED, 'started_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::RUNNING,   'started_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::FAILED,    'started_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::STALE,     'started_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::SUCCEEDED, 'started_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::RUNNING,   'started_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::FAILED,    'started_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::STALE,     'started_at' => Carbon::now()->subDays(15)]);

        self::assertSame(2, Monitor::query()->where('status', MonitorStatus::STALE)->count());

        $this->artisan('queue-monitor:stale --beforeInterval=P30D');

        self::assertSame(3, Monitor::query()->where('status', MonitorStatus::STALE)->count());
    }

    public function testDry()
    {
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::SUCCEEDED, 'started_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::RUNNING,   'started_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::FAILED,    'started_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::STALE,     'started_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::SUCCEEDED, 'started_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::RUNNING,   'started_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::FAILED,    'started_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::STALE,     'started_at' => Carbon::now()->subDays(15)]);

        self::assertSame(2, Monitor::query()->where('status', MonitorStatus::STALE)->count());

        $this->artisan('queue-monitor:stale --beforeDays=30 --dry');

        self::assertSame(2, Monitor::query()->where('status', MonitorStatus::STALE)->count());
    }
}
