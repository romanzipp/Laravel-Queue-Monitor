<?php

namespace romanzipp\QueueMonitor\Traits;

use Exception;
use romanzipp\QueueMonitor\Models\Monitor;

trait QueueMonitor
{
    /**
     * Update progress
     * @param  int    $progress Progress as integer 0-100
     * @return void
     */
    public function queueProgress(int $progress)
    {
        if ($progress < 0 || $progress > 100) {
            throw new Exception('Progress value must be between 0 and 100');
        }

        $monitor = $this->getQueueMonitor();

        if ($monitor == null) {
            return;
        }

        $monitor->update([
            'progress' => $progress,
        ]);
    }

    /**
     * Set Monitor data
     * @param  mixed $data Custom data
     * @return void
     */
    public function queueData($data)
    {
        $monitor = $this->getQueueMonitor();

        if ($monitor == null) {
            return;
        }

        $monitor->update([
            'data' => serialize($data),
        ]);
    }

    /**
     * Return Queue Monitor Model
     * @return Monitor|null
     */
    private function getQueueMonitor()
    {
        if (method_exists($this->job, 'getJobId') && $this->job->getJobId()) {
            $jobId = $this->job->getJobId();
        } else {
            $jobId = sha1($this->job->getRawBody());
        }

        $monitor = Monitor::whereJob($jobId)
            ->orderBy('started_at', 'desc')
            ->limit(1)
            ->first();

        return $monitor;
    }
}
