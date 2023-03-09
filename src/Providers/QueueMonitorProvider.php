<?php

namespace romanzipp\QueueMonitor\Providers;

use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use romanzipp\QueueMonitor\Console\Commands\PurgeOldMonitorsCommand;
use romanzipp\QueueMonitor\Models\Monitor;
use romanzipp\QueueMonitor\Services\QueueMonitor;

class QueueMonitorProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/queue-monitor.php' => config_path('queue-monitor.php'),
            ], 'config');

            $this->publishes([
                __DIR__ . '/../../migrations' => database_path('migrations'),
            ], 'migrations');

            $this->publishes([
                __DIR__ . '/../../views' => resource_path('views/vendor/queue-monitor'),
            ], 'views');

            $this->commands([
                PurgeOldMonitorsCommand::class,
            ]);
        }

        $this->loadViewsFrom(
            __DIR__ . '/../../views',
            'queue-monitor'
        );

        if (config('queue-monitor.ui.enabled')) {
            Route::group(config('queue-monitor.ui.route'), function () {
                $this->loadRoutesFrom(__DIR__ . '/../../routes/queue-monitor.php');
            });
        }

        /** @var QueueManager $manager */
        $manager = app(QueueManager::class);

        $manager->before(static function (JobProcessing $event) {
            QueueMonitor::handleJobProcessing($event);
        });

        $manager->after(static function (JobProcessed $event) {
            QueueMonitor::handleJobProcessed($event);
        });

        $manager->failing(static function (JobFailed $event) {
            QueueMonitor::handleJobFailed($event);
        });

        $manager->exceptionOccurred(static function (JobExceptionOccurred $event) {
            QueueMonitor::handleJobExceptionOccurred($event);
        });
    }

    public function register(): void
    {
        /** @phpstan-ignore-next-line */
        if ( ! $this->app->configurationIsCached()) {
            $this->mergeConfigFrom(
                __DIR__ . '/../../config/queue-monitor.php',
                'queue-monitor'
            );
        }

        QueueMonitor::$model = config('queue-monitor.model') ?: Monitor::class;
    }
}
