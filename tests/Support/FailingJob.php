<?php

namespace romanzipp\QueueMonitor\Tests\Support;

class FailingJob extends Job
{
    public function handle(): void
    {
        $this->job->markAsFailed();

        // throw new Exception('Whoops');
    }
}
