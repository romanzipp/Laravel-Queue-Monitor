<?php

namespace romanzipp\QueueMonitor\Contracts;

interface MonitoredJobContract
{
    public function queueProgress(int $progress): void;

    public function queueProgressChunk(int $collectionCount, int $perChunk): void;

    /**
     * @param array<mixed, mixed> $data
     * @param bool $merge
     *
     * @return void
     */
    public function queueData(array $data, bool $merge = false): void;

    public static function keepMonitorOnSuccess(): bool;

    public function progressCooldown(): int;

    /**
     * @return array<mixed, mixed>|null
     */
    public function initialMonitorData(): ?array;
}
