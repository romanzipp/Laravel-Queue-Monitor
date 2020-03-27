<?php

namespace romanzipp\QueueMonitor\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

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
 * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\QueueMonitor\Models\Monitor whereJob()
 * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\QueueMonitor\Models\Monitor ordered()
 * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\QueueMonitor\Models\Monitor lastHour()
 * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\QueueMonitor\Models\Monitor today()
 * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\QueueMonitor\Models\Monitor failed()
 * @method static \Illuminate\Database\Eloquent\Builder|\romanzipp\QueueMonitor\Models\Monitor succeeded()
 */
class Monitor extends Model
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

    /**
     * Scopes
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

    /**
     * Methods
     */

    public function startedAtExact(): Carbon
    {
        return Carbon::parse($this->started_at_exact);
    }

    public function finishedAtExact(): Carbon
    {
        return Carbon::parse($this->finished_at_exact);
    }

    public function getBasenameAttribute()
    {
        return Arr::last(explode('\\', $this->name));
    }

    public function getParsedDataAttribute(): array
    {
        return json_decode($this->data, true) ?? [];
    }

    public function getRemainingSecondsAttribute(): ?float
    {
        if ($this->isFinished()) {
            return null;
        }

        if ($this->progress === null) {
            return null;
        }

        if ( ! $this->started_at) {
            return null;
        }

        $secondsRunning = now()->getTimestamp() - $this->started_at->getTimestamp();

        return (float) ($secondsRunning - ($secondsRunning * $this->progress / 100));
    }

    public function basename()
    {
        return $this->basename;
    }

    /**
     * Determine weather job is finished.
     *
     * @return boolean
     */
    public function isFinished(): bool
    {
        if ($this->failed) {
            return true;
        }

        return $this->finished_at !== null;
    }

    /**
     * Determine weather job has succeeded.
     *
     * @return boolean
     */
    public function isSucceeded(): bool
    {
        if ( ! $this->isFinished()) {
            return false;
        }

        return $this->failed == false;
    }
}
