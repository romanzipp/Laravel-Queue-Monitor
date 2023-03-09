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

    private function createMonitor(Carbon $startedAt, int $progress = null): Monitor
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
