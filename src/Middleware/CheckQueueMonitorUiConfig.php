<?php

namespace romanzipp\QueueMonitor\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CheckQueueMonitorUiConfig
{
    public function handle(Request $request, \Closure $next)
    {
        if ( ! config('queue-monitor.ui.enabled')) {
            throw new NotFoundHttpException('Not Found');
        }

        return $next($request);
    }
}
