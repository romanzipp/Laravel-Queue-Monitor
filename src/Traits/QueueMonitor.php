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
    public function queueProgress(int $progress): void
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
     * @param  array $data Custom data
     * @return void
     */
    public function queueData(array $data): void
    {
        $monitor = $this->getQueueMonitor();

        if ($monitor == null) {
            return;
        }

        $monitor->update([
            'data' => json_encode($data),
        ]);
    }

    /**
     * Return Queue Monitor Model
     * @return Monitor|null
     */
    private function getQueueMonitor()
    {
        $jobId = value(function () {

            if (method_exists($this->job, 'getJobId') && $this->job->getJobId()) {

                return $this->job->getJobId();
            }

            if (method_exists($this->job, 'getRawBody')) {

                return sha1($this->job->getRawBody());
            }

            return null;
        });

        if ($jobId === null) {
            return null;
        }

        $monitor = Monitor::whereJob($jobId)
            ->orderBy('started_at', 'desc')
            ->limit(1)
            ->first();

        return $monitor;
    }
}
