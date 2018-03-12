# Laravel Queue Monitor

[![Latest Stable Version](https://poser.pugx.org/romanzipp/laravel-queue-monitor/version)](https://packagist.org/packages/romanzipp/laravel-queue-monitor)
[![Total Downloads](https://poser.pugx.org/romanzipp/laravel-queue-monitor/downloads)](https://packagist.org/packages/romanzipp/laravel-queue-monitor)
[![License](https://poser.pugx.org/romanzipp/laravel-queue-monitor/license)](https://packagist.org/packages/romanzipp/laravel-queue-monitor)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/romanzipp/Laravel-Queue-Monitor/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/romanzipp/Laravel-Queue-Monitor/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/romanzipp/Laravel-Queue-Monitor/badges/build.png?b=master)](https://scrutinizer-ci.com/g/romanzipp/Laravel-Queue-Monitor/build-status/master)

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

Or add `romanzipp/laravel-queue-monitor` to your `composer.json`

```
"romanzipp/laravel-queue-monitor": "*"
```

Run composer update to pull the latest version.

**If you use Laravel 5.5+ you are already done, otherwise continue:**

```php
romanzipp\QueueMonitor\Providers\QueueMonitorProvider::class,
```

Add Service Provider to your app.php configuration file:

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

The package automaticly logs **all dispatched jobs**.

### Exclude job from beding monitored

```php
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use romanzipp\QueueMonitor\Traits\DontMonitor; // <---

class ExampleJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use DontMonitor; // <---

    public function __construct()
    {
        //
    }

    public function handle()
    {
        //
    }
}
```

### Update Job Progress / Custom Data

You can update the progress of the current job, like supported by FFMpeg

```php
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use romanzipp\QueueMonitor\Traits\QueueMonitor; // <--- Queue Monitor data

class ExampleJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use QueueMonitor; // <---

    public function __construct()
    {
        //
    }

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
