<?php

namespace romanzipp\QueueMonitor\Tests;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use romanzipp\QueueMonitor\Models\Monitor;

class RemainingSecondsCalculationTest extends TestCase
{
    public function testFirstCalculation()
    {
        $monitor = $this->createMonitor(Carbon::parse('2020-01-01 10:00:00'), 50);

        $this->assertEquals(30, $monitor->getRemainingSeconds(Carbon::parse('2020-01-01 10:00:30')));
        $this->assertEquals(30, $monitor->getRemainingInterval(Carbon::parse('2020-01-01 10:00:30'))->totalSeconds);
    }

    public function testSecondCalculation()
    {
        $monitor = $this->createMonitor(Carbon::parse('2020-01-01 10:00:00'), 5);

        $this->assertEquals(19, $monitor->getRemainingSeconds(Carbon::parse('2020-01-01 10:00:01')));
        $this->assertEquals(19, $monitor->getRemainingInterval(Carbon::parse('2020-01-01 10:00:01'))->totalSeconds);
    }

    public function testThirdCalculation()
    {
        $monitor = $this->createMonitor(Carbon::parse('2020-01-01 10:00:00'), 1);
        $this->assertEquals(495, $monitor->getRemainingSeconds(Carbon::parse('2020-01-01 10:00:05')));
        $this->assertEquals(495, $monitor->getRemainingInterval(Carbon::parse('2020-01-01 10:00:05'))->totalSeconds);
    }

    public function createMonitor(Carbon $startedAt, int $progress): Monitor
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
