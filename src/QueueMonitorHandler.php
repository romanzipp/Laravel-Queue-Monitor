<?php

namespace romanzipp\QueueMonitor;

use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Carbon;
use romanzipp\QueueMonitor\Models\Monitor;
use romanzipp\QueueMonitor\Traits\QueueMonitor;

class QueueMonitorHandler
{
    /**
     * Handle Job Processing.
     *
     * @param JobProcessing $event
     * @return void
     */
    public function handleJobProcessing(JobProcessing $event): void
    {
        $this->jobStarted($event->job);
    }

    /**
     * Handle Job Processed.
     *
     * @param JobProcessed $event
     * @return void
     */
    public function handleJobProcessed(JobProcessed $event): void
    {
        $this->jobFinished($event->job);
    }

    /**
     * Handle Job Failing.
     *
     * @param JobFailed $event
     * @return void
     */
    public function handleJobFailed(JobFailed $event): void
    {
        $this->jobFinished($event->job, true);
    }

    /**
     * Handle Job Exception Occurred.
     *
     * @param JobExceptionOccurred $event
     * @return void
     */
    public function handleJobExceptionOccurred(JobExceptionOccurred $event): void
    {
        $this->jobFinished($event->job, true, $event->exception);
    }

    /**
     * Get Job ID.
     *
     * @param Job $job
     * @return string|int
     */
    public static function getJobId(Job $job)
    {
        if ($jobId = $job->getJobId()) {
            return $jobId;
        }

        return sha1($job->getRawBody());
    }

    /**
     * Start Queue Monitoring for Job.
     *
     * @param Job $job
     * @return void
     */
    protected function jobStarted(Job $job): void
    {
        if ( ! $this->shouldBeMonitored($job)) {
            return;
        }

        $now = Carbon::now();

        Monitor::query()->create([
            'job_id' => self::getJobId($job),
            'name' => $job->resolveName(),
            'queue' => $job->getQueue(),
            'started_at' => $now,
            'started_at_exact' => $now->format('Y-m-d H:i:s.u'),
            'attempt' => $job->attempts(),
        ]);
    }

    /**
     * Finish Queue Monitoring for Job.
     *
     * @param Job $job
     * @param boolean $failed
     * @param mixed $exception
     * @return void
     */
    protected function jobFinished(Job $job, bool $failed = false, $exception = null): void
    {
        if ( ! $this->shouldBeMonitored($job)) {
            return;
        }

        $monitor = Monitor::query()
            ->where('job_id', self::getJobId($job))
            ->orderBy('started_at', 'desc')
            ->first();

        if ($monitor === null) {
            return;
        }

        $now = Carbon::now();

        $startedAt = Carbon::parse($monitor->started_at_exact);

        $timeElapsed = (float) $startedAt->diffInSeconds($now) + $startedAt->diff($now)->f;

        Monitor::query()
            ->where('id', $monitor->id)
            ->update([
                'finished_at' => $now,
                'finished_at_exact' => $now->format('Y-m-d H:i:s.u'),
                'time_elapsed' => $timeElapsed,
                'failed' => $failed,
                'exception' => $exception ? $exception->getMessage() : null,
            ]);
    }

    /**
     * Determine weather the Job should be monitored, default true.
     *
     * @param Job $job
     * @return bool
     */
    protected function shouldBeMonitored(Job $job): bool
    {
        return array_key_exists(QueueMonitor::class, class_uses_recursive(
            $job->resolveName()
        ));
    }
}
