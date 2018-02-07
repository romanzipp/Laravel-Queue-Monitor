<?php

namespace romanzipp\QueueMonitor\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Monitor extends Model
{
    protected $fillable = [
        'job_id',
        'name',
        'queue',
        'started_at',
        'started_at_exact',
        'finished_at',
        'finished_at_exact',
        'time_elapsed',
        'failed',
        'attempt',
        'exception',
        'progress',
        'data',
    ];

    protected $casts = [
        'failed' => 'boolean',
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
    
    public function scopeWhereJob($query, $jobId)
    {
        return $query->where('job_id', $jobId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('started_at', 'desc');
    }

    public function scopeLastHour($query)
    {
        return $query->where('started_at', '<', Carbon::now()->subHours(1));
    }

    public function scopeToday($query)
    {
        return $query->whereRaw('DATE(started_at) = ?', [Carbon::now()->subHours(1)->format('Y-m-d')]);
    }

    public function scopeFailed($query)
    {
        return $query->where('failed', true);
    }

    public function scopeSucceeded($query)
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

    public function basename()
    {
        return basename($this->name);
    }

    public function isFinished()
    {
        if ($this->failed) {
            return true;
        }

        return $this->finished_at !== null;
    }

    public function isSucceeded()
    {
        if (!$this->isFinished()) {
            return false;
        }

        return $this->failed == false;
    }
}
