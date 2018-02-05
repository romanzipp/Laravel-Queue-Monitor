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

    public function scopeOrdered($query)
    {
        return $query->orderBy('started_at', 'asc');
    }

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
}
