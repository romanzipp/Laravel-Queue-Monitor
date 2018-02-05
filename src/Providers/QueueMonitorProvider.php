<?php

namespace romanzipp\QueueMonitor\Providers;

use Illuminate\Support\ServiceProvider;

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
            dirname(__DIR__) . '/../queue-monitor.php' => config_path('queue-monitor.php'),
        ], 'config');

        $this->loadMigrationsFrom(
            dirname(__DIR__) . '/../migrations'
        );
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/../queue-monitor.php', 'queue-monitor'
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [Twitch::class];
    }
}
