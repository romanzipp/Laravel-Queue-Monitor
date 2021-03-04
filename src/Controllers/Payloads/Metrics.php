<?php

namespace romanzipp\QueueMonitor\Controllers\Payloads;

final class Metrics
{
    /**
     * @var \romanzipp\QueueMonitor\Controllers\Payloads\Metric[]
     */
    public $metrics = [];

    /**
     * @return \romanzipp\QueueMonitor\Controllers\Payloads\Metric[]
     */
    public function all(): array
    {
        return $this->metrics;
    }

    public function push(Metric $metric): self
    {
        $this->metrics[] = $metric;

        return $this;
    }
}
