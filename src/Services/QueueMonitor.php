<?php

namespace romanzipp\QueueMonitor\Services;

use Illuminate\Contracts\Queue\Job as JobContract;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobQueued;
use Illuminate\Support\Carbon;
use romanzipp\QueueMonitor\Enums\MonitorStatus;
use romanzipp\QueueMonitor\Models\Contracts\MonitorContract;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class QueueMonitor
{
    private const TIMESTAMP_EXACT_FORMAT = 'Y-m-d H:i:s.u';

    public static string $model;

    /**
     * Get the model used to store the monitoring data.
     *
     * @return MonitorContract
     */
    public static function getModel(): MonitorContract
    {
        return new self::$model();
    }

    /**
     * Handle Job Queued.
     *
     * @param JobQueued $event
     *
     * @return void
     */
    public static function handleJobQueued(JobQueued $event): void
    {
        self::jobQueued($event);
    }

    /**
     * Handle Job Processing.
     *
     * @param JobProcessing $event
     *
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
     *
     * @return void
     */
    public static function handleJobProcessed(JobProcessed $event): void
    {
        self::jobFinished($event->job, MonitorStatus::SUCCEEDED);
    }

    /**
     * Handle Job Failing.
     *
     * @param JobFailed $event
     *
     * @return void
     */
    public static function handleJobFailed(JobFailed $event): void
    {
        self::jobFinished($event->job, MonitorStatus::FAILED, $event->exception);
    }

    /**
     * Handle Job Exception Occurred.
     *
     * @param JobExceptionOccurred $event
     *
     * @return void
     */
    public static function handleJobExceptionOccurred(JobExceptionOccurred $event): void
    {
        self::jobFinished($event->job, MonitorStatus::FAILED, $event->exception);
    }

    /**
     * Get Job ID.
     *
     * @param JobContract $job
     *
     * @return string|int
     */
    public static function getJobId(JobContract $job): string|int
    {
        if ($jobId = $job->getJobId()) {
            return $jobId;
        }

        return sha1($job->getRawBody());
    }

    /**
     * Start Queue Monitoring for Job.
     *
     * @param JobQueued $event
     *
     * @return void
     */
    protected static function jobQueued(JobQueued $event): void
    {
        if ( ! self::shouldBeMonitored($event->job)) {
            return;
        }

        // add initial data
        if (method_exists($event->job, 'initialMonitorData')) {
            $data = json_encode($event->job->initialMonitorData());
        }

        QueueMonitor::getModel()::query()->create([
            'job_id' => $event->id,
            'name' => get_class($event->job),
            /** @phpstan-ignore-next-line */
            'queue' => $event->job->queue ?: 'default',
            'status' => MonitorStatus::QUEUED,
            'queued_at' => now(),
            'data' => $data ?? null,
        ]);
    }

    /**
     * Job Start Processing.
     *
     * @param JobContract $job
     *
     * @return void
     */
    protected static function jobStarted(JobContract $job): void
    {
        if ( ! self::shouldBeMonitored($job)) {
            return;
        }

        $now = Carbon::now();

        $model = self::getModel();

        /** @var MonitorContract $monitor */
        $monitor = $model::query()->updateOrCreate([
            'job_id' => $jobId = self::getJobId($job),
            'queue' => $job->getQueue() ?: 'default',
            'status' => MonitorStatus::QUEUED,
        ], [
            'job_uuid' => $job->uuid(),
            'name' => $job->resolveName(),
            'started_at' => $now,
            'started_at_exact' => $now->format(self::TIMESTAMP_EXACT_FORMAT),
            'attempt' => $job->attempts(),
            'status' => MonitorStatus::RUNNING,
        ]);

        // Mark jobs with same job id (different execution) as stale
        $model::query()
            ->where('id', '!=', $monitor->id)
            ->where('job_id', $jobId)
            ->where('status', '!=', MonitorStatus::FAILED)
            ->whereNull('finished_at')
            ->each(function (MonitorContract $monitor) {
                $monitor->update([
                    'finished_at' => $now = Carbon::now(),
                    'finished_at_exact' => $now->format(self::TIMESTAMP_EXACT_FORMAT),
                    'status' => MonitorStatus::STALE,
                ]);
            });
    }

    /**
     * Finish Queue Monitoring for Job.
     *
     * @param JobContract $job
     * @param int $status
     * @param \Throwable|null $exception
     *
     * @return void
     */
    protected static function jobFinished(JobContract $job, int $status, ?\Throwable $exception = null): void
    {
        if ( ! self::shouldBeMonitored($job)) {
            return;
        }

        $model = self::getModel();

        /** @var MonitorContract|null $monitor */
        $monitor = $model::query()
            ->where('job_id', self::getJobId($job))
            ->where('attempt', $job->attempts())
            ->orderByDesc('started_at')
            ->first();

        if (null === $monitor) {
            return;
        }

        $now = Carbon::now();

        $resolvedJob = $job->resolveName();

        if (null === $exception && false === $resolvedJob::keepMonitorOnSuccess()) {
            $monitor->delete();

            return;
        }

        // if the job has an exception, but it's not failed (it did not exceed max tries and max exceptions),
        // so it will be back to the queue
        if (MonitorStatus::FAILED == $status && ! $job->hasFailed()) {
            $status = MonitorStatus::QUEUED;
        }

        // if the job is processed, but it's released, so it will be back to the queue also
        if (MonitorStatus::SUCCEEDED == $status && $job->isReleased()) {
            $status = MonitorStatus::QUEUED;
        }

        $attributes = [
            'finished_at' => $now,
            'finished_at_exact' => $now->format(self::TIMESTAMP_EXACT_FORMAT),
            'status' => $status,
        ];

        if (null !== $exception) {
            $attributes += [
                'exception' => mb_strcut((string) $exception, 0, config('queue-monitor.db_max_length_exception', 4294967295)),
                'exception_class' => get_class($exception),
                'exception_message' => mb_strcut($exception->getMessage(), 0, config('queue-monitor.db_max_length_exception_message', 65535)),
            ];
        }

        $monitor->update($attributes);
    }

    /**
     * Determine weather the Job should be monitored, default true.
     *
     * @param object|JobContract $job
     *
     * @return bool
     */
    public static function shouldBeMonitored(object $job): bool
    {
        $class = $job instanceof JobContract ? $job->resolveName() : $job;

        return array_key_exists(IsMonitored::class, ClassUses::classUsesRecursive($class));
    }
}
