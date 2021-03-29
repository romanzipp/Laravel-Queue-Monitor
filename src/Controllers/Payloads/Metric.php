<?php

namespace romanzipp\QueueMonitor\Controllers\Payloads;

final class Metric
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var int
     */
    public $value;

    /**
     * @var int
     */
    public $previousValue;

    /**
     * @var string
     */
    public $format;

    public function __construct(string $title, int $value = 0, int $previousValue = null, string $format = '%d')
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
