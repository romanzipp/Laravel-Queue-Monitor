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
    private const TIMESTAMP_EXACT_FORMAT = 'Y-m-d H:i:s.u';

    /**
     * Handle Job Processing.
     *
     * @param JobProcessing $event
     * @return void
     */
    public static function handleJobProcessing(JobProcessing $event): void
    {
        self::jobStarted($event->job);
    }

    /**
     * Handle Job Processed.
     *
     * @param JobProcessed $event
     * @return void
     */
    public static function handleJobProcessed(JobProcessed $event): void
    {
        self::jobFinished($event->job);
    }

    /**
     * Handle Job Failing.
     *
     * @param JobFailed $event
     * @return void
     */
    public static function handleJobFailed(JobFailed $event): void
    {
        self::jobFinished($event->job, true);
    }

    /**
     * Handle Job Exception Occurred.
     *
     * @param JobExceptionOccurred $event
     * @return void
     */
    public static function handleJobExceptionOccurred(JobExceptionOccurred $event): void
    {
        self::jobFinished($event->job, true, $event->exception);
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
    protected static function jobStarted(Job $job): void
    {
        if ( ! self::shouldBeMonitored($job)) {
            return;
        }

        $now = Carbon::now();

        Monitor::query()->create([
            'job_id' => self::getJobId($job),
            'name' => $job->resolveName(),
            'queue' => $job->getQueue(),
            'started_at' => $now,
            'started_at_exact' => $now->format(self::TIMESTAMP_EXACT_FORMAT),
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
    protected static function jobFinished(Job $job, bool $failed = false, $exception = null): void
    {
        if ( ! self::shouldBeMonitored($job)) {
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
                'finished_at_exact' => $now->format(self::TIMESTAMP_EXACT_FORMAT),
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
    public static function shouldBeMonitored(Job $job): bool
    {
        return array_key_exists(QueueMonitor::class, class_uses_recursive(
            $job->resolveName()
        ));
    }
}
