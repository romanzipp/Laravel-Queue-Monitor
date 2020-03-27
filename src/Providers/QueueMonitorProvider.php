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

        /** @var QueueManager $manager */
        $manager = app(QueueManager::class);

        /** @var QueueManager $manager */
        $handler = app(QueueMonitorHandler::class);

        $manager->before(static function (JobProcessing $event) use ($handler) {
            $handler->handleJobProcessing($event);
        });

        $manager->after(static function (JobProcessed $event) use ($handler) {
            $handler->handleJobProcessed($event);
        });

        $manager->failing(static function (JobFailed $event) use ($handler) {
            $handler->handleJobFailed($event);
        });

        $manager->exceptionOccurred(static function (JobExceptionOccurred $event) use ($handler) {
            $handler->handleJobExceptionOccurred($event);
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
