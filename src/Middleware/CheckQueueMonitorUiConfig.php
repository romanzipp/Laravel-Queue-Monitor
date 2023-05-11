<?php

namespace romanzipp\QueueMonitor\Middleware;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CheckQueueMonitorUiConfig
{
    public function handle(Request $request, \Closure $next): mixed
    {
        $route = $request->route();

        if ( ! ($route instanceof Route)) {
            throw new NotFoundHttpException('Not Found');
        }

        $allowed = match ($route->getName()) {
            'queue-monitor::index' => config('queue-monitor.ui.enabled'),
            'queue-monitor::retry' => config('queue-monitor.ui.enabled') && config('queue-monitor.ui.allow_retry'),
            'queue-monitor::destroy' => config('queue-monitor.ui.enabled') && config('queue-monitor.ui.allow_deletion'),
            'queue-monitor::purge' => config('queue-monitor.ui.enabled') && config('queue-monitor.ui.allow_purge'),
            default => false
        };

        if ( ! $allowed) {
            throw new NotFoundHttpException('Not Found');
        }

        return $next($request);
    }
}
