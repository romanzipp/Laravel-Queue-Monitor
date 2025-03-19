<?php

namespace romanzipp\QueueMonitor\Providers;

use Illuminate\Config\Repository;
use Illuminate\Queue\Events as QueueEvents;
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
        /** @var \Illuminate\Config\Repository $config */
        $config = $this->app['config'];

        /** @var \Illuminate\Events\Dispatcher $events */
        $events = $this->app['events'];

        /** @var \Illuminate\Queue\QueueManager $queueManager */
        $queueManager = $this->app->make(QueueManager::class);

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

        $this->loadViewsFrom(__DIR__ . '/../../views', 'queue-monitor');

        if ($config->boolean('queue-monitor.ui.enabled', false)) {
            Route::group(self::buildRouteGroupConfig($config), function () {
                $this->loadRoutesFrom(__DIR__ . '/../../routes/queue-monitor.php');
            });
        }

        // Listen to queue job events

        /**
         * If the project uses Horizon, we will listen to the JobPushed event,
         * because Horizon fires JobPushed event when the job is queued or retry the job again from its UI.
         *
         * @see https://laravel.com/docs/horizon
         */
        if (class_exists('Laravel\Horizon\Events\JobPushed')) {
            $events->listen('Laravel\Horizon\Events\JobPushed', function ($event) {
                QueueMonitor::handleJobPushed($event);
            });
        } else {
            $events->listen(QueueEvents\JobQueued::class, function (QueueEvents\JobQueued $event) {
                QueueMonitor::handleJobQueued($event);
            });
        }

        $queueManager->before(static function (QueueEvents\JobProcessing $event) {
            QueueMonitor::handleJobProcessing($event);
        });

        $queueManager->after(static function (QueueEvents\JobProcessed $event) {
            QueueMonitor::handleJobProcessed($event);
        });

        $queueManager->failing(static function (QueueEvents\JobFailed $event) {
            QueueMonitor::handleJobFailed($event);
        });

        $queueManager->exceptionOccurred(static function (QueueEvents\JobExceptionOccurred $event) {
            QueueMonitor::handleJobExceptionOccurred($event);
        });


        $this->app['events']->listen([
            QueueEvents\JobExceptionOccurred::class,
            QueueEvents\JobFailed::class,
            QueueEvents\JobPopped::class,
            QueueEvents\JobPopping::class,
            QueueEvents\JobProcessed::class,
            QueueEvents\JobProcessing::class,
            QueueEvents\JobQueued::class,
            QueueEvents\JobQueueing::class,
            QueueEvents\JobReleasedAfterException::class,
            QueueEvents\JobRetryRequested::class,
            QueueEvents\JobTimedOut::class,
            QueueEvents\Looping::class,
            QueueEvents\QueueBusy::class,
            QueueEvents\WorkerStopping::class,
        ], function ($event) {
            #dump(get_class($event));
        });
    }

    /**
     * @return array<string, mixed>
     */
    public static function buildRouteGroupConfig(Repository $config): array
    {
        $routeConfig = $config->array('queue-monitor.ui.route');

        if ( ! isset($routeConfig['middleware'])) {
            $routeConfig['middleware'] = [];
        }

        $routeConfig['middleware'][] = CheckQueueMonitorUiConfig::class;

        return $routeConfig;
    }

    public function register(): void
    {
        /** @var \Illuminate\Config\Repository $config */
        $config = $this->app['config'];

        $this->mergeConfigFrom(
            __DIR__ . '/../../config/queue-monitor.php',
            'queue-monitor'
        );

        QueueMonitor::$model = $config->get('queue-monitor.model') ?: Monitor::class;
    }
}
