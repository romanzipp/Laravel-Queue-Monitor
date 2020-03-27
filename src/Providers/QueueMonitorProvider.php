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
        if ($this->app->runningInConsole()) {

            $this->publishes([
                dirname(__DIR__) . '/../config/queue-monitor.php' => config_path('queue-monitor.php'),
            ], 'config');

            $this->loadMigrationsFrom(
                dirname(__DIR__) . '/../migrations'
            );
        }

        /** @var QueueManager $manager */
        $manager = app(QueueManager::class);

        $manager->before(static function (JobProcessing $event) {
            QueueMonitorHandler::handleJobProcessing($event);
        });

        $manager->after(static function (JobProcessed $event) {
            QueueMonitorHandler::handleJobProcessed($event);
        });

        $manager->failing(static function (JobFailed $event) {
            QueueMonitorHandler::handleJobFailed($event);
        });

        $manager->exceptionOccurred(static function (JobExceptionOccurred $event) {
            QueueMonitorHandler::handleJobExceptionOccurred($event);
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if ( ! $this->app->configurationIsCached()) {
            $this->mergeConfigFrom(
                dirname(__DIR__) . '/../config/queue-monitor.php', 'queue-monitor'
            );
        }
    }
}
