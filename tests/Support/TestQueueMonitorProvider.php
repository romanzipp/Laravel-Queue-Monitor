<?php

namespace romanzipp\QueueMonitor\Tests\Support;

use Illuminate\Support\Facades\Route;
use romanzipp\QueueMonitor\Middleware\CheckQueueMonitorUiConfig;
use romanzipp\QueueMonitor\Providers\QueueMonitorProvider;

class TestQueueMonitorProvider extends QueueMonitorProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom([
            __DIR__ . '/../../migrations',
        ]);

        parent::boot();

        // Always load routes in test environment
        Route::group($this->buildTestRouteGroupConfig(), function () {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/queue-monitor.php');
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function buildTestRouteGroupConfig(): array
    {
        $config = config('queue-monitor.ui.route', []);

        if ( ! isset($config['middleware'])) {
            $config['middleware'] = [];
        }

        $config['middleware'][] = CheckQueueMonitorUiConfig::class;

        return $config;
    }
}
