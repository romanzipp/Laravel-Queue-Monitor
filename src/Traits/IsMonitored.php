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
     * Internal variable used for tracking chunking progress.
     *
     * @var int
     */
    private $progressCurrentChunk = 0;

    /**
     * Update progress.
     *
     * @param int $progress Progress as integer 0-100
     * @return void
     */
    public function queueProgress(int $progress): void
    {
        if ($progress < 0) {
            $progress = 0;
        }

        if ($progress > 100) {
            $progress = 100;
        }

        if ( ! $monitor = $this->getQueueMonitor()) {
            return;
        }

        $monitor->update([
            'progress' => $progress,
        ]);
    }

    /**
     * Automatically update the current progress in each chunk iteration.
     *
     * @param int $collectionCount The total collection item amount
     * @param int $perChunk The size of each chunk
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
}
