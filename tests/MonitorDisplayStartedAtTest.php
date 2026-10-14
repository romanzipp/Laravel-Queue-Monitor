<?php

namespace romanzipp\QueueMonitor\Tests;

use Illuminate\Support\Carbon;
use romanzipp\QueueMonitor\Models\Monitor;
use romanzipp\QueueMonitor\Tests\TestCases\DatabaseTestCase;

class MonitorDisplayStartedAtTest extends DatabaseTestCase
{
    public function testDisplayStartedAtReturnsNullWhenNotStarted(): void
    {
        $monitor = new Monitor();

        self::assertNull($monitor->getDisplayStartedAt());
    }

    public function testDisplayStartedAtPrefersStartedAtExact(): void
    {
        config(['queue-monitor.monitor_timezone' => 'America/New_York']);

        $monitor = new Monitor();
        $monitor->setRawAttributes([
            'started_at' => '2020-01-01 15:00:00',
            'started_at_exact' => '2020-01-01 10:00:00.000000',
        ]);

        $expected = Carbon::parse('2020-01-01 10:00:00.000000', 'America/New_York');

        self::assertTrue($expected->equalTo($monitor->getDisplayStartedAt()));
    }

    public function testDisplayStartedAtUsesDatabaseTimezoneForStartedAtColumn(): void
    {
        config([
            'app.timezone' => 'UTC',
            'queue-monitor.database_timezone' => 'America/New_York',
        ]);

        $monitor = new Monitor();
        $monitor->setRawAttributes([
            'started_at' => '2020-01-01 10:00:00',
            'started_at_exact' => null,
        ]);

        $expected = Carbon::parse('2020-01-01 10:00:00', 'America/New_York');

        self::assertTrue($expected->equalTo($monitor->getDisplayStartedAt()));
    }

    public function testDisplayStartedAtDiffForHumansUsesPastTime(): void
    {
        Carbon::setTestNow(Carbon::parse('2020-01-01 12:00:00', 'UTC'));

        config([
            'app.timezone' => 'UTC',
            'queue-monitor.database_timezone' => 'UTC',
        ]);

        $monitor = new Monitor();
        $monitor->setRawAttributes([
            'started_at' => '2020-01-01 10:00:00',
            'started_at_exact' => null,
        ]);

        self::assertStringContainsString('ago', $monitor->getDisplayStartedAt()->diffForHumans());

        Carbon::setTestNow();
    }

    public function testFormatDisplayStartedAtIncludesTimezone(): void
    {
        config(['queue-monitor.monitor_timezone' => 'America/New_York']);

        $monitor = new Monitor();
        $monitor->setRawAttributes([
            'started_at_exact' => '2020-01-01 10:00:00.000000',
        ]);

        self::assertSame(
            '2020-01-01 10:00:00 America/New_York',
            $monitor->formatDisplayStartedAt()
        );
    }
}
