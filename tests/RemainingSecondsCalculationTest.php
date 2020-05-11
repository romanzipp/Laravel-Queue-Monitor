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
        $this->assertEquals('30s', $monitor->getRemainingInterval(Carbon::parse('2020-01-01 10:00:30'))->forHumans(null, true));
    }

    public function testSecondCalculation()
    {
        $monitor = $this->createMonitor(Carbon::parse('2020-01-01 10:00:00'), 5);

        $this->assertEquals(19, $monitor->getRemainingSeconds(Carbon::parse('2020-01-01 10:00:01')));
        $this->assertEquals('19s', $monitor->getRemainingInterval(Carbon::parse('2020-01-01 10:00:01'))->forHumans(null, true));
    }

    public function testThirdCalculation()
    {
        $monitor = $this->createMonitor(Carbon::parse('2020-01-01 10:00:00'), 1);
        $this->assertEquals(495, $monitor->getRemainingSeconds(Carbon::parse('2020-01-01 10:00:05')));
        $this->assertEquals('8m 15s', $monitor->getRemainingInterval(Carbon::parse('2020-01-01 10:00:05'))->forHumans(null, true));
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
