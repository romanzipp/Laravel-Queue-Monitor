<?php

namespace romanzipp\QueueMonitor\Enums;

class MonitorStatus
{
    public const RUNNING = 0;
    public const SUCCEEDED = 1;
    public const FAILED = 2;
    public const STALE = 3;
}
