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
        'finished_at',
        'time_elapsed',
        'failed',
        'attempt',
        'exception',
    ];

    protected $casts = [
        'failed' => 'boolean',
    ];

    protected $dates = [];

    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('queue-monitor.table'));
    }

    public function startedAtExact(): Carbon
    {
        return Carbon::parse($this->started_at_exact);
    }

    public function finishedAtExact(): Carbon
    {
        return Carbon::parse($this->finished_at_exact);
    }
}
