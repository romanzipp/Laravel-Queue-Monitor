# Laravel Queue Monitor

This package offers monitoring like "Laravel Horizon" for database queue.

The Idea has been inspirated by gilbitron's [laravel-queue-monitor](https://github.com/gilbitron/laravel-queue-monitor) package.

## Enhancements

* Support for milliseconds
* Model for Queue Monitorings

## Configuration

Copy configuration to config folder:

```
$ php artisan vendor:publish --provider=romanzipp\QueueMonitor\Providers\QueueMonitorProvider
```

Migrate the Queue Monitoring table. The table name itself can be configured in the config file.

```
$ php artisan migrate
```

## Usage

The package automaticly logs all dispatched jobs.

Retrieve processed Jobs:

```php
use romanzipp\QueueMonitor\Models\Monitor;

$jobs = Monitor::ordered()->get();

foreach ($jobs as $job) {

    // Exact start & finish dates with milliseconds
    $job->startedAtExact();
    $job->finishedAtExact();
}
```

## To do

* Add Job & Artisan Command for automatic cleanup of old database entries
