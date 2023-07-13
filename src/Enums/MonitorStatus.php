<?php

namespace romanzipp\QueueMonitor\Enums;

class MonitorStatus
{
    public const RUNNING = 0;
    public const SUCCEEDED = 1;
    public const FAILED = 2;
    public const STALE = 3;
    public const QUEUED = 4;

    /**
     * @return int[]
     */
    public static function toArray(): array
    {
        return [
            self::RUNNING,
            self::SUCCEEDED,
            self::FAILED,
            self::STALE,
            self::QUEUED,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function toNamedArray(): array
    {
        return [
            self::RUNNING => 'Running',
            self::SUCCEEDED => 'Succeeded',
            self::FAILED => 'Failed',
            self::STALE => 'Stale',
            self::QUEUED => 'Queued',
        ];
    }
}
