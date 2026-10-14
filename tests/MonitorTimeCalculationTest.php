<?php

namespace romanzipp\QueueMonitor\Tests;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use romanzipp\QueueMonitor\Models\Monitor;
use romanzipp\QueueMonitor\Tests\TestCases\DatabaseTestCase;

class MonitorTimeCalculationTest extends DatabaseTestCase
{
    public function testRemaingSeconds()
    {
        self::assertEquals(
            30,
            $this
                ->createMonitor(Carbon::parse('2020-01-01 10:00:00'), 50)
                ->getRemainingSeconds(Carbon::parse('2020-01-01 10:00:30'))
        );

        self::assertEquals(
            19,
            $this
                ->createMonitor(Carbon::parse('2020-01-01 10:00:00'), 5)
                ->getRemainingSeconds(Carbon::parse('2020-01-01 10:00:01'))
        );

        self::assertEquals(
            495,
            $this
                ->createMonitor(Carbon::parse('2020-01-01 10:00:00'), 1)
                ->getRemainingSeconds(Carbon::parse('2020-01-01 10:00:05'))
        );
    }

    public function testElaspedSeconds()
    {
        self::assertEquals(
            30,
            $this
                ->createMonitor(Carbon::parse('2020-01-01 10:00:00'))
                ->getElapsedSeconds(Carbon::parse('2020-01-01 10:00:30'))
        );

        self::assertEquals(
            1,
            $this
                ->createMonitor(Carbon::parse('2020-01-01 10:00:00'))
                ->getElapsedSeconds(Carbon::parse('2020-01-01 10:00:01'))
        );

        self::assertEquals(
            5,
            $this
                ->createMonitor(Carbon::parse('2020-01-01 10:00:00'))
                ->getElapsedSeconds(Carbon::parse('2020-01-01 10:00:05'))
        );
    }

    public function testElapsedSecondsInterval()
    {
        self::assertEquals(
            '00:00:05',
            $this
                ->createMonitor(Carbon::parse('2020-01-01 10:00:00'))
                ->getElapsedInterval(Carbon::parse('2020-01-01 10:00:05'))
                ->format('%H:%I:%S')
        );

        self::assertEquals(
            '01:00:00',
            $this
                ->createMonitor(Carbon::parse('2020-01-01 10:00:00'))
                ->getElapsedInterval(Carbon::parse('2020-01-01 11:00:00'))
                ->format('%H:%I:%S')
        );
    }

    public function testDisplayQueuedAtUsesMonitorTimezone()
    {
        config([
            'app.timezone' => 'America/New_York',
            'queue-monitor.monitor_timezone' => 'America/New_York',
            'queue-monitor.database_timezone' => 'UTC',
        ]);

        Carbon::setTestNow(Carbon::parse('2025-06-22 12:00:00', 'America/New_York'));

        /** @var Monitor $monitor */
        $monitor = Monitor::query()->create([
            'job_id' => sha1(Str::random()),
            'queued_at' => now(),
        ]);

        $monitor->refresh();

        self::assertLessThan(
            60,
            $monitor->getDisplayQueuedAt()->diffInSeconds(Carbon::now())
        );

        Carbon::setTestNow();
    }

    public function testElapsedIntervalUsesMonitorTimezoneForRunningJobs()
    {
        config([
            'app.timezone' => 'UTC',
            'queue-monitor.monitor_timezone' => 'America/New_York',
        ]);

        Carbon::setTestNow(Carbon::parse('2020-01-01 15:00:05', 'UTC'));

        $monitor = new Monitor();
        $monitor->setRawAttributes([
            'started_at_exact' => '2020-01-01 10:00:00.000000',
        ]);

        self::assertEquals(
            '00:00:05',
            $monitor->getElapsedInterval()->format('%H:%I:%S')
        );

        Carbon::setTestNow();
    }

    public function testElapsedIntervalUsesMonitorTimezoneForFinishedJobs()
    {
        config([
            'app.timezone' => 'UTC',
            'queue-monitor.monitor_timezone' => 'America/New_York',
            'queue-monitor.database_timezone' => 'UTC',
        ]);

        $monitor = new Monitor();
        $monitor->setRawAttributes([
            'started_at_exact' => '2020-01-01 10:00:00.000000',
            'finished_at' => '2020-01-01 15:00:05',
            'finished_at_exact' => null,
        ]);

        self::assertEquals(
            '00:00:05',
            $monitor->getElapsedInterval()->format('%H:%I:%S')
        );
    }

    public function testDisplayQueuedAtCorrectsLegacyTimezoneSkew()
    {
        config([
            'app.timezone' => 'UTC',
            'queue-monitor.monitor_timezone' => 'UTC',
        ]);

        Carbon::setTestNow(Carbon::parse('2026-06-22 17:56:00', 'UTC'));

        /** @var Monitor $monitor */
        $monitor = Monitor::query()->create([
            'job_id' => sha1(Str::random()),
            'queued_at' => '2026-06-22 12:45:16',
            'started_at' => Carbon::parse('2026-06-22 17:45:16', 'UTC'),
            'started_at_exact' => '2026-06-22 17:45:16.000000',
        ]);

        $monitor->refresh();

        self::assertLessThan(
            900,
            $monitor->getDisplayQueuedAt()->diffInSeconds(Carbon::now())
        );

        self::assertGreaterThan(
            300,
            $monitor->getDisplayQueuedAt()->diffInSeconds(Carbon::now())
        );

        Carbon::setTestNow();
    }

    private function createMonitor(Carbon $startedAt, ?int $progress = null): Monitor
    {
        /** @var Monitor $monitor */
        $monitor = Monitor::query()->create([
            'job_id' => sha1(Str::random()),
            'started_at' => $startedAt,
            'started_at_exact' => $startedAt,
            'progress' => $progress,
        ]);

        return $monitor;
    }
}
