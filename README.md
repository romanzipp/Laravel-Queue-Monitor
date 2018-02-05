# Laravel Queue Monitor

This package offers monitoring like "Laravel Horizon" for database queue.

The Idea has been inspirated by gilbitron's [laravel-queue-monitor](https://github.com/gilbitron/laravel-queue-monitor) package.

## Enhancements

* Support for milliseconds
* Model for Queue Monitorings

## Installation

```
composer require romanzipp/laravel-queue-monitor
```

Or add `romanzipp/laravel-queue-monitor` to your `composer.json`

```
"romanzipp/laravel-twitch": "dev-master"
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
```

## To do

* Add Job & Artisan Command for automatic cleanup of old database entries
