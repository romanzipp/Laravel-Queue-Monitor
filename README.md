# Laravel Queue Monitor

[![Latest Stable Version](https://img.shields.io/packagist/v/romanzipp/laravel-queue-monitor.svg?style=flat-square)](https://packagist.org/packages/romanzipp/laravel-queue-monitor)
[![Total Downloads](https://img.shields.io/packagist/dt/romanzipp/laravel-queue-monitor.svg?style=flat-square)](https://packagist.org/packages/romanzipp/laravel-queue-monitor)
[![License](https://img.shields.io/packagist/l/romanzipp/laravel-queue-monitor.svg?style=flat-square)](https://packagist.org/packages/romanzipp/laravel-queue-monitor)
[![Code Quality](https://img.shields.io/scrutinizer/g/romanzipp/laravel-queue-monitor.svg?style=flat-square)](https://scrutinizer-ci.com/g/romanzipp/laravel-queue-monitor/?branch=master)
[![Build Status](https://img.shields.io/scrutinizer/build/g/romanzipp/laravel-queue-monitor.svg?style=flat-square)](https://scrutinizer-ci.com/g/romanzipp/Laravel-Queue-Monitor/build-status/master)

This package offers monitoring like [Laravel Horizon](https://laravel.com/docs/horizon) for database queue.

## Features

* Monitor all jobs like [Laravel Horizon](https://laravel.com/docs/horizon), but not only for redis
* Handles failed jobs with exception
* Support for milliseconds
* Model for Queue Monitorings

## Installation

```
composer require romanzipp/laravel-queue-monitor
```

**If you use Laravel 5.5+ you are already done, otherwise continue.**

Add Service Provider to your app.php configuration file:

```php
romanzipp\QueueMonitor\Providers\QueueMonitorProvider::class,
```

## Configuration

Copy configuration to config folder:

```
$ php artisan vendor:publish --provider="romanzipp\QueueMonitor\Providers\QueueMonitorProvider"
```

Migrate the Queue Monitoring table. The table name itself can be configured in the config file.

```
$ php artisan migrate
```

## Usage

To monitor a job, add the `romanzipp\QueueMonitor\Traits\QueueMonitor` Trait.

### Update Job Progress / Custom Data

You can update the progress of the current job, like supported by FFMpeg

```php
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use romanzipp\QueueMonitor\Traits\QueueMonitor; // <---

class ExampleJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use QueueMonitor; // <---

    public function handle()
    {
        // Save progress, if job driver supports
        $ffmpeg->on('progress', function ($percentage) {

            $this->queueProgress($percentage);
        });

        // Save data if finished. Must be type of array
        $this->queueData(['foo' => 'bar']);
    }
}
```

### Retrieve processed Jobs

```php
use romanzipp\QueueMonitor\Models\Monitor;

$jobs = Monitor::ordered()->get();

foreach ($jobs as $job) {

    // Exact start & finish dates with milliseconds
    $job->startedAtExact();
    $job->finishedAtExact();
}
```

### Model Scopes

```php
// Filter by Status
Monitor::failed();
Monitor::succeeded();

// Filter by Date
Monitor::lastHour();
Monitor::today();

// Chain Scopes
Monitor::today()->failed();

// Get parsed custom Monitor data

$monitor = Monitor::find(1);

$monitor->data; // Raw data string

$monitor->parsed_data; // JSON decoded data, always array
```

## ToDo

* Add Job & Artisan Command for automatic cleanup of old database entries

----

The Idea has been inspirated by gilbitron's [laravel-queue-monitor](https://github.com/gilbitron/laravel-queue-monitor) package.

## Upgrading

### Monitor Model

```diff
- ->basename()
- ->basename
+ ->getBaseame()

- ->parsed_data
+ ->getData()

- remaing_seconds
+ getRemainingSeconds()

- startedAtExact()
+ getStartedAtExact()

- finishedAtExact()
+ getFinishedAtExact()
```
