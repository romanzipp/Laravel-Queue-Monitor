<?php

namespace romanzipp\QueueMonitor\Providers;

use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider;
use romanzipp\QueueMonitor\QueueMonitorHandler;

class QueueMonitorProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            dirname(__DIR__) . '/../config/queue-monitor.php' => config_path('queue-monitor.php'),
        ], 'config');

        $this->loadMigrationsFrom(
            dirname(__DIR__) . '/../migrations'
        );

        app(QueueManager::class)->before(function (JobProcessing $event) {
            app(QueueMonitorHandler::class)->handleJobProcessing($event);
        });

        app(QueueManager::class)->after(function (JobProcessed $event) {
            app(QueueMonitorHandler::class)->handleJobProcessed($event);
        });

        app(QueueManager::class)->failing(function (JobFailed $event) {
            app(QueueMonitorHandler::class)->handleJobFailed($event);
        });

        app(QueueManager::class)->exceptionOccurred(function (JobExceptionOccurred $event) {
            app(QueueMonitorHandler::class)->handleJobExceptionOccurred($event);
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/../config/queue-monitor.php', 'queue-monitor'
        );
    }
}
