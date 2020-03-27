<?php

namespace romanzipp\QueueMonitor\Tests\Support;

abstract class BaseJob
{
    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        //
    }

    public function getJob()
    {
        return $this->job;
    }
}
