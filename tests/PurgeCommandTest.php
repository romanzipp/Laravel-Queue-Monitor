<?php

namespace romanzipp\QueueMonitor\Tests;

use Carbon\Carbon;
use romanzipp\QueueMonitor\Enums\MonitorStatus;
use romanzipp\QueueMonitor\Models\Monitor;
use romanzipp\QueueMonitor\Tests\TestCases\DatabaseTestCase;

class PurgeCommandTest extends DatabaseTestCase
{
    protected function tearDown(): void
    {
        Monitor::query()->truncate();

        parent::tearDown();
    }

    public function testDate()
    {
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::SUCCEEDED, 'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::RUNNING,   'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::FAILED,    'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::STALE,     'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::SUCCEEDED, 'queued_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::RUNNING,   'queued_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::FAILED,    'queued_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::STALE,     'queued_at' => Carbon::now()->subDays(15)]);

        self::assertSame(8, Monitor::query()->count());

        $this->artisan('queue-monitor:purge --before=' . Carbon::now()->subDays(30)->format('Y-m-d'));

        self::assertSame(4, Monitor::query()->count());
    }

    public function testDateChunked()
    {
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::SUCCEEDED, 'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::RUNNING,   'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::FAILED,    'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::STALE,     'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::SUCCEEDED, 'queued_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::RUNNING,   'queued_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::FAILED,    'queued_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::STALE,     'queued_at' => Carbon::now()->subDays(15)]);

        self::assertSame(8, Monitor::query()->count());

        $this->artisan('queue-monitor:purge --chunk --before=' . Carbon::now()->subDays(30)->format('Y-m-d'));

        self::assertSame(4, Monitor::query()->count());
    }

    public function testDays()
    {
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::SUCCEEDED, 'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::RUNNING,   'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::FAILED,    'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::STALE,     'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::SUCCEEDED, 'queued_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::RUNNING,   'queued_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::FAILED,    'queued_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::STALE,     'queued_at' => Carbon::now()->subDays(15)]);

        self::assertSame(8, Monitor::query()->count());

        $this->artisan('queue-monitor:purge --beforeDays=30');

        self::assertSame(4, Monitor::query()->count());
    }

    public function testDaysChunked()
    {
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::SUCCEEDED, 'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::RUNNING,   'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::FAILED,    'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::STALE,     'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::SUCCEEDED, 'queued_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::RUNNING,   'queued_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::FAILED,    'queued_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::STALE,     'queued_at' => Carbon::now()->subDays(15)]);

        self::assertSame(8, Monitor::query()->count());

        $this->artisan('queue-monitor:purge --chunk --beforeDays=30');

        self::assertSame(4, Monitor::query()->count());
    }

    public function testInterval()
    {
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::SUCCEEDED, 'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::RUNNING,   'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::FAILED,    'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::STALE,     'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::SUCCEEDED, 'queued_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::RUNNING,   'queued_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::FAILED,    'queued_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::STALE,     'queued_at' => Carbon::now()->subDays(15)]);

        self::assertSame(8, Monitor::query()->count());

        $this->artisan('queue-monitor:purge --beforeInterval=P30D');

        self::assertSame(4, Monitor::query()->count());
    }

    public function testIntervalChunked()
    {
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::SUCCEEDED, 'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::RUNNING,   'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::FAILED,    'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::STALE,     'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::SUCCEEDED, 'queued_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::RUNNING,   'queued_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::FAILED,    'queued_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::STALE,     'queued_at' => Carbon::now()->subDays(15)]);

        self::assertSame(8, Monitor::query()->count());

        $this->artisan('queue-monitor:purge --chunk --beforeInterval=P30D');

        self::assertSame(4, Monitor::query()->count());
    }

    public function testDateOnlySuccessfull()
    {
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::SUCCEEDED, 'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::RUNNING,   'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::FAILED,    'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::STALE,     'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::SUCCEEDED, 'queued_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::RUNNING,   'queued_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::FAILED,    'queued_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::STALE,     'queued_at' => Carbon::now()->subDays(15)]);

        self::assertSame(8, Monitor::query()->count());

        $this->artisan('queue-monitor:purge --only-succeeded --before=' . Carbon::now()->subDays(30)->format('Y-m-d'));

        self::assertSame(7, Monitor::query()->count());
    }

    public function testDaysOnlySuccessfull()
    {
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::SUCCEEDED, 'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::RUNNING,   'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::FAILED,    'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::STALE,     'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::SUCCEEDED, 'queued_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::RUNNING,   'queued_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::FAILED,    'queued_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::STALE,     'queued_at' => Carbon::now()->subDays(15)]);

        self::assertSame(8, Monitor::query()->count());

        $this->artisan('queue-monitor:purge --only-succeeded --beforeDays=30');

        self::assertSame(7, Monitor::query()->count());
    }

    public function testIntervalOnlySuccessfull()
    {
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::SUCCEEDED, 'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::RUNNING,   'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::FAILED,    'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::STALE,     'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::SUCCEEDED, 'queued_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::RUNNING,   'queued_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::FAILED,    'queued_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::STALE,     'queued_at' => Carbon::now()->subDays(15)]);

        self::assertSame(8, Monitor::query()->count());

        $this->artisan('queue-monitor:purge --beforeInterval=P30D');

        self::assertSame(4, Monitor::query()->count());
    }

    public function testQueues()
    {
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::SUCCEEDED, 'queued_at' => Carbon::now()->subDays(15), 'queue' => 'foobar']);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::RUNNING,   'queued_at' => Carbon::now()->subDays(15), 'queue' => 'foobar']);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::FAILED,    'queued_at' => Carbon::now()->subDays(15), 'queue' => 'foobar']);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::STALE,     'queued_at' => Carbon::now()->subDays(15), 'queue' => 'foobar']);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::SUCCEEDED, 'queued_at' => Carbon::now()->subDays(15), 'queue' => 'bar']);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::RUNNING,   'queued_at' => Carbon::now()->subDays(15), 'queue' => 'bar']);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::FAILED,    'queued_at' => Carbon::now()->subDays(15), 'queue' => 'foo']);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::STALE,     'queued_at' => Carbon::now()->subDays(15), 'queue' => 'foo']);

        self::assertSame(8, Monitor::query()->count());

        $this->artisan('queue-monitor:purge --queue=foo,bar --beforeDays=5');

        self::assertSame(4, Monitor::query()->count());
    }

    public function testDry()
    {
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::SUCCEEDED, 'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::RUNNING,   'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::FAILED,    'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::STALE,     'queued_at' => Carbon::now()->subDays(60)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::SUCCEEDED, 'queued_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::RUNNING,   'queued_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::FAILED,    'queued_at' => Carbon::now()->subDays(15)]);
        Monitor::query()->create(['job_id' => 'foo', 'status' => MonitorStatus::STALE,     'queued_at' => Carbon::now()->subDays(15)]);

        self::assertSame(8, Monitor::query()->count());

        $this->artisan('queue-monitor:purge --dry --before=' . Carbon::now()->subDays(30)->format('Y-m-d'));

        self::assertSame(8, Monitor::query()->count());
    }
}
