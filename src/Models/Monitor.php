<?php

namespace romanzipp\QueueMonitor\Models;

use Carbon\CarbonInterval;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use romanzipp\QueueMonitor\Models\Contracts\MonitorContract;
use Throwable;

/**
 * @property int $id
 * @property string $job_id
 * @property string|null $name
 * @property string|null $queue
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property string|null $started_at_exact
 * @property \Illuminate\Support\Carbon|null $finished_at
 * @property string|null $finished_at_exact
 * @property float $time_elapsed
 * @property bool $failed
 * @property int $attempt
 * @property int|null $progress
 * @property string|null $exception
 * @property string|null $exception_class
 * @property string|null $exception_message
 * @property string|null $data
 *
 * @method static Builder|Monitor whereJob()
 * @method static Builder|Monitor ordered()
 * @method static Builder|Monitor lastHour()
 * @method static Builder|Monitor today()
 * @method static Builder|Monitor failed()
 * @method static Builder|Monitor succeeded()
 */
class Monitor extends Model implements MonitorContract
{
    protected $guarded = [];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'failed' => 'bool',
    ];

    /**
     * @var string[]
     */
    protected $dates = [
        'started_at',
        'finished_at',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('queue-monitor.table'));

        if ($connection = config('queue-monitor.connection')) {
            $this->setConnection($connection);
        }
    }

    /*
     *--------------------------------------------------------------------------
     * Scopes
     *--------------------------------------------------------------------------
     */

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|int $jobId
     */
    public function scopeWhereJob(Builder $query, $jobId): void
    {
        $query->where('job_id', $jobId);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query
            ->orderBy('started_at', 'desc')
            ->orderBy('started_at_exact', 'desc');
    }

    public function scopeLastHour(Builder $query): void
    {
        $query->where('started_at', '>', Carbon::now()->subHours(1));
    }

    public function scopeToday(Builder $query): void
    {
        $query->whereRaw('DATE(started_at) = ?', [Carbon::now()->subHours(1)->format('Y-m-d')]);
    }

    public function scopeFailed(Builder $query): void
    {
        $query->where('failed', true);
    }

    public function scopeSucceeded(Builder $query): void
    {
        $query->where('failed', false);
    }

    /*
     *--------------------------------------------------------------------------
     * Methods
     *--------------------------------------------------------------------------
     */

    public function getStartedAtExact(): ?Carbon
    {
        if (null === $this->started_at_exact) {
            return null;
        }

        return Carbon::parse($this->started_at_exact);
    }

    public function getFinishedAtExact(): ?Carbon
    {
        if (null === $this->finished_at_exact) {
            return null;
        }

        return Carbon::parse($this->finished_at_exact);
    }

    /**
     * Get the estimated remaining seconds. This requires a job progress to be set.
     *
     * @param \Illuminate\Support\Carbon|null $now
     *
     * @return float
     */
    public function getRemainingSeconds(Carbon $now = null): float
    {
        return $this->getRemainingInterval($now)->totalSeconds;
    }

    public function getRemainingInterval(Carbon $now = null): CarbonInterval
    {
        if (null === $now) {
            $now = Carbon::now();
        }

        if ( ! $this->progress || null === $this->started_at || $this->isFinished()) {
            return CarbonInterval::seconds(0);
        }

        if (0 === ($timeDiff = $now->getTimestamp() - $this->started_at->getTimestamp())) {
            return CarbonInterval::seconds(0);
        }

        return CarbonInterval::seconds(
            (100 - $this->progress) / ($this->progress / $timeDiff)
        )->cascade();
    }

    /**
     * Get the currently elapsed seconds.
     *
     * @param \Illuminate\Support\Carbon|null $end
     *
     * @return float
     */
    public function getElapsedSeconds(Carbon $end = null): float
    {
        return $this->getElapsedInterval($end)->seconds;
    }

    public function getElapsedInterval(Carbon $end = null): CarbonInterval
    {
        if (null === $end) {
            $end = $this->getFinishedAtExact() ?? $this->finished_at ?? Carbon::now();
        }

        $startedAt = $this->getStartedAtExact() ?? $this->started_at;

        if (null === $startedAt) {
            return CarbonInterval::seconds(0);
        }

        return $startedAt->diffAsCarbonInterval($end);
    }

    /**
     * Get any optional data that has been added to the monitor model within the job.
     *
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return json_decode($this->data, true) ?? [];
    }

    /**
     * Recreate the exception.
     *
     * @param bool $rescue Wrap the exception recreation to catch exceptions
     *
     * @return \Throwable|null
     */
    public function getException(bool $rescue = true): ?Throwable
    {
        if (null === $this->exception_class) {
            return null;
        }

        if ( ! $rescue) {
            return new $this->exception_class($this->exception_message);
        }

        try {
            return new $this->exception_class($this->exception_message);
        } catch (Exception $exception) {
            return null;
        }
    }

    /**
     * Get the base class name of the job.
     *
     * @return string|null
     */
    public function getBasename(): ?string
    {
        if (null === $this->name) {
            return null;
        }

        return Arr::last(explode('\\', $this->name));
    }

    /**
     * check if the job is finished.
     *
     * @return bool
     */
    public function isFinished(): bool
    {
        if ($this->hasFailed()) {
            return true;
        }

        return null !== $this->finished_at;
    }

    /**
     * Check if the job has failed.
     *
     * @return bool
     */
    public function hasFailed(): bool
    {
        return true === $this->failed;
    }

    /**
     * check if the job has succeeded.
     *
     * @return bool
     */
    public function hasSucceeded(): bool
    {
        if ( ! $this->isFinished()) {
            return false;
        }

        return ! $this->hasFailed();
    }
}
