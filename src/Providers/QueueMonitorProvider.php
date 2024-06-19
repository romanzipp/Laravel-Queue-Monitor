<?php

namespace romanzipp\QueueMonitor\Providers;

use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobQueued;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use romanzipp\QueueMonitor\Console\Commands\MarkJobsAsStaleCommand;
use romanzipp\QueueMonitor\Console\Commands\PurgeOldMonitorsCommand;
use romanzipp\QueueMonitor\Middleware\CheckQueueMonitorUiConfig;
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
                __DIR__ . '/../../migrations-upgrade' => base_path('migrations-upgrade'),
            ], 'migrations-upgrade');

            $this->publishes([
                __DIR__ . '/../../views' => resource_path('views/vendor/queue-monitor'),
            ], 'views');

            $this->publishes([
                __DIR__ . '/../../dist' => public_path('vendor/queue-monitor'),
            ], 'assets');

            $this->commands([
                MarkJobsAsStaleCommand::class,
                PurgeOldMonitorsCommand::class,
            ]);
        }

        $this->loadViewsFrom(
            __DIR__ . '/../../views',
            'queue-monitor'
        );

        Route::group($this->buildRouteGroupConfig(), function () {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/queue-monitor.php');
        });

        // listen to JobQueued event
        if (config('queue-monitor.monitor_queued_jobs', true)) {
            /**
             * If the project uses Horizon, we will listen to the JobPushed event,
             * because Horizon fires JobPushed event when the job is queued or retry the job again from its UI
             *
             * @see https://laravel.com/docs/horizon
             */
            if (class_exists('Laravel\Horizon\Events\JobPushed')) {
                Event::listen('Laravel\Horizon\Events\JobPushed', function ($event) {
                    QueueMonitor::handleJobPushed($event);
                });
            } else {
                Event::listen(JobQueued::class, function (JobQueued $event) {
                    QueueMonitor::handleJobQueued($event);
                });
            }
        }

        // listen to other job events

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

    /**
     * @return array<string, mixed>
     */
    private function buildRouteGroupConfig(): array
    {
        $config = config('queue-monitor.ui.route');

        if ( ! isset($config['middleware'])) {
            $config['middleware'] = [];
        }

        $config['middleware'][] = CheckQueueMonitorUiConfig::class;

        return $config;
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
