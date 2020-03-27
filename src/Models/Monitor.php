<?php

namespace romanzipp\QueueMonitor\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use romanzipp\QueueMonitor\Models\Contracts\MonitorContract;

/**
 * @property int id
 * @property string $job_id
 * @property string|null $name
 * @property string|null $queue
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property string|null $started_at_exact
 * @property \Illuminate\Support\Carbon|null $finished_at
 * @property string|null $finished_at_exact
 * @property float $time_elapsed
 * @property boolean $failed
 * @property integer $attempt
 * @property integer|null $progress
 * @property string|null $exception
 * @property string|null $data
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

    protected $casts = [
        'failed' => 'bool',
    ];

    protected $dates = [
        'started_at',
        'finished_at',
    ];

    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('queue-monitor.table'));
    }

    /*
     *--------------------------------------------------------------------------
     * Scopes
     *--------------------------------------------------------------------------
     */

    public function scopeWhereJob(Builder $query, $jobId)
    {
        return $query->where('job_id', $jobId);
    }

    public function scopeOrdered(Builder $query)
    {
        return $query
            ->orderBy('started_at', 'desc')
            ->orderBy('started_at_exact', 'desc');
    }

    public function scopeLastHour(Builder $query)
    {
        return $query->where('started_at', '>', Carbon::now()->subHours(1));
    }

    public function scopeToday(Builder $query)
    {
        return $query->whereRaw('DATE(started_at) = ?', [Carbon::now()->subHours(1)->format('Y-m-d')]);
    }

    public function scopeFailed(Builder $query)
    {
        return $query->where('failed', true);
    }

    public function scopeSucceeded(Builder $query)
    {
        return $query->where('failed', false);
    }

    /*
     *--------------------------------------------------------------------------
     * Methods
     *--------------------------------------------------------------------------
     */

    public function getStartedAtExact(): ?Carbon
    {
        if ($this->started_at_exact === null) {
            return null;
        }

        return Carbon::parse($this->started_at_exact);
    }

    public function getFinishedAtExact(): ?Carbon
    {
        if ($this->finished_at_exact === null) {
            return null;
        }

        return Carbon::parse($this->finished_at_exact);
    }

    public function getRemainingSeconds(): float
    {
        if ($this->isFinished()) {
            return 0.0;
        }

        if ($this->progress === null) {
            return 0.0;
        }

        if ( ! $this->started_at) {
            return 0.0;
        }

        $secondsRunning = Carbon::now()->getTimestamp() - $this->started_at->getTimestamp();

        return ($secondsRunning - ($secondsRunning * $this->progress / 100));
    }

    /**
     * Get any optional data that has been added to the monitor model within the job.
     *
     * @return array
     */
    public function getData(): array
    {
        return json_decode($this->data, true) ?? [];
    }

    /**
     * Get the base class name of the job.
     *
     * @return string|null
     */
    public function getBasename(): ?string
    {
        if ($this->name === null) {
            return null;
        }

        return Arr::last(explode('\\', $this->name));
    }

    /**
     * check if the job is finished.
     *
     * @return boolean
     */
    public function isFinished(): bool
    {
        if ($this->hasFailed()) {
            return true;
        }

        return $this->finished_at !== null;
    }

    /**
     * Check if the job has failed.
     *
     * @return bool
     */
    public function hasFailed(): bool
    {
        return $this->failed === true;
    }

    /**
     * check if the job has succeeded.
     *
     * @return boolean
     */
    public function hasSucceeded(): bool
    {
        if ( ! $this->isFinished()) {
            return false;
        }

        return ! $this->hasFailed();
    }
}
