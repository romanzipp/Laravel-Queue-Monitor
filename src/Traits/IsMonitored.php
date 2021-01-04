<?php

namespace romanzipp\QueueMonitor\Traits;

use romanzipp\QueueMonitor\Models\Contracts\MonitorContract;
use romanzipp\QueueMonitor\Services\QueueMonitor;

/**
 * @mixin \Illuminate\Queue\InteractsWithQueue
 */
trait IsMonitored
{
    /**
     * The unix timestamp explaining the last time a progress has been written to database.
     *
     * @var int|null
     */
    private $progressLastUpdated = null;

    /**
     * Internal variable used for tracking chunking progress.
     *
     * @var int
     */
    private $progressCurrentChunk = 0;

    /**
     * Update progress.
     *
     * @param int $progress Progress as integer 0-100
     *
     * @return void
     */
    public function queueProgress(int $progress): void
    {
        $progress = min(100, max(0, $progress));

        if ( ! $monitor = $this->getQueueMonitor()) {
            return;
        }

        if ($this->isQueueProgressOnCooldown($progress)) {
            return;
        }

        $monitor->update([
            'progress' => $progress,
        ]);

        $this->progressLastUpdated = time();
    }

    /**
     * Automatically update the current progress in each chunk iteration.
     *
     * @param int $collectionCount The total collection item amount
     * @param int $perChunk The size of each chunk
     *
     * @return void
     */
    public function queueProgressChunk(int $collectionCount, int $perChunk): void
    {
        $this->queueProgress(
            ++$this->progressCurrentChunk * $perChunk / $collectionCount * 100
        );
    }

    /**
     * Set Monitor data.
     *
     * @param array $data Custom data
     * @param bool $merge Merge the data instead of overriding
     *
     * @return void
     */
    public function queueData(array $data, bool $merge = false): void
    {
        if ( ! $monitor = $this->getQueueMonitor()) {
            return;
        }

        if ($merge) {
            $data = array_merge($monitor->getData(), $data);
        }

        $monitor->update([
            'data' => json_encode($data),
        ]);
    }

    /**
     * Check if the monitor should skip writing the progress to database avoiding rapid update queries.
     * The progress values 0, 25, 50, 75 and 100 will always be written.
     *
     * @param int $progress
     *
     * @return bool
     */
    private function isQueueProgressOnCooldown(int $progress): bool
    {
        if (in_array($progress, [0, 25, 50, 75, 100])) {
            return false;
        }

        if (null === $this->progressLastUpdated) {
            return false;
        }

        return time() - $this->progressLastUpdated < $this->progressCooldown();
    }

    /**
     * Delete Queue Monitor object.
     *
     * @return void
     */
    protected function deleteQueueMonitor(): void
    {
        if ( ! $monitor = $this->getQueueMonitor()) {
            return;
        }

        $monitor->delete();
    }

    /**
     * Return Queue Monitor Model.
     *
     * @return MonitorContract|null
     */
    protected function getQueueMonitor(): ?MonitorContract
    {
        if ( ! property_exists($this, 'job')) {
            return null;
        }

        if ( ! $this->job) {
            return null;
        }

        if ( ! $jobId = QueueMonitor::getJobId($this->job)) {
            return null;
        }

        $model = QueueMonitor::getModel();

        return $model::whereJob($jobId)
            ->orderBy('started_at', 'desc')
            ->first();
    }

    /**
     * Weather to keep successful monitor models. This can be used if you only want to keep
     * failed monitors for jobs that are frequently executed but worth to monitor. You are free
     * to use the Laravel built-in failed job procedures.
     *
     * @return bool
     */
    public static function keepMonitorOnSuccess(): bool
    {
        return true;
    }

    /**
     * The time in seconds to wait before a following queue progress update will be issued.
     * This is used to avoid writing many progress updates to the database. 0 = no delay.
     *
     * @return int
     */
    public function progressCooldown(): int
    {
        return 0;
    }
}
