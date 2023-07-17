<?php

namespace romanzipp\QueueMonitor\Controllers\Payloads;

final class Metric
{
    public string $title;

    public float $value;

    public ?int $previousValue;

    public string $format;

    public function __construct(string $title, float $value = 0, int $previousValue = null, string $format = '%d')
    {
        $this->title = $title;
        $this->value = $value;
        $this->previousValue = $previousValue;
        $this->format = $format;
    }

    public function hasChanged(): bool
    {
        return $this->value !== $this->previousValue;
    }

    public function hasIncreased(): bool
    {
        return $this->value > $this->previousValue;
    }

    public function format(int $value): string
    {
        return sprintf($this->format, $value);
    }
}
