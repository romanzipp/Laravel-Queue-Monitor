<?php

namespace romanzipp\QueueMonitor;

use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Support\Carbon;
use romanzipp\QueueMonitor\Models\Monitor;

class QueueMonitorHandler
{
    /**
     * Handle Job Processing
     * @param  JobProcessing $event
     * @return void
     */
    public function handleJobProcessing(JobProcessing $event)
    {
        $this->jobStarted($event->job);
    }

    /**
     * Handle Job Processed
     * @param  JobProcessed $event
     * @return void
     */
    public function handleJobProcessed(JobProcessed $event)
    {
        $this->jobFinished($event->job);
    }

    /**
     * Handle Job Failing
     * @param  JobFailed $event
     * @return void
     */
    public function handleJobFailed(JobFailed $event)
    {
        $this->jobFinished($event->job, true);
    }

    /**
     * Handle Job Exception Occurred
     * @param  JobExceptionOccurred $event
     * @return void
     */
    public function handleJobExceptionOccurred(JobExceptionOccurred $event)
    {
        $this->jobFinished($event->job, true, $event->exception);
    }

    /**
     * Get Job ID
     * @param  Job    $job
     * @return string|int
     */
    protected function getJobId(Job $job)
    {
        if (method_exists($job, 'getJobId') && $job->getJobId()) {
            return $job->getJobId();
        }

        return sha1($job->getRawBody());
    }

    /**
     * Start Queue Monitoring for Job
     * @param  Job    $job
     * @return void
     */
    protected function jobStarted(Job $job)
    {
        if (!$this->shouldBeMonitored($job)) {
            return;
        }

        $now = Carbon::now();

        Monitor::create([
            'job_id' => $this->getJobId($job),
            'name' => $job->resolveName(),
            'queue' => $job->getQueue(),
            'started_at' => $now,
            'started_at_exact' => $now->format('Y-m-d H:i:s.u'),
            'attempt' => $job->attempts(),
        ]);
    }

    /**
     * Finish Queue Monitoring for Job
     * @param  Job     $job
     * @param  boolean $failed
     * @param  mixed   $exception
     * @return void
     */
    protected function jobFinished(Job $job, bool $failed = false, $exception = null)
    {
        if (!$this->shouldBeMonitored($job)) {
            return;
        }

        $monitor = Monitor::where('job_id', $this->getJobId($job))
            ->orderBy('started_at', 'desc')
            ->limit(1)
            ->first();

        if ($monitor == null) {
            return;
        }

        $now = Carbon::now();

        $startedAt = Carbon::parse($monitor->started_at);

        $timeElapsed = (float) $now->diffInSeconds($startedAt) + $now->diff($startedAt)->f;

        Monitor::where('id', $monitor->id)->update([
            'finished_at' => $now,
            'finished_at_exact' => $now->format('Y-m-d H:i:s.u'),
            'time_elapsed' => $timeElapsed,
            'failed' => $failed,
            'exception' => $exception ? $exception->getMessage() : null,
        ]);
    }

    /**
     * Determine wether the Job should be monitored, default true
     * @param  Job    $job
     * @return bool
     */
    protected function shouldBeMonitored(Job $job): bool
    {
        return method_exists($job, 'getQueueMonitor') == false && method_exists($job, 'getRawBody');
    }
}
