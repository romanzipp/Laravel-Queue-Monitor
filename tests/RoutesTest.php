<?php

namespace romanzipp\QueueMonitor\Tests;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route as RouteFacade;
use romanzipp\QueueMonitor\Controllers\ShowQueueMonitorController;
use romanzipp\QueueMonitor\Tests\TestCases\DatabaseTestCase;

class RoutesTest extends DatabaseTestCase
{
    public function testBasicRouteCreation()
    {
        RouteFacade::prefix('jobs')->group(function () {
            RouteFacade::queueMonitor();
        });

        self::assertInstanceOf(Route::class, $route = app(Router::class)->getRoutes()->getByAction(ShowQueueMonitorController::class));

        self::assertEquals('jobs', $route->uri);
        self::assertEquals('\romanzipp\QueueMonitor\Controllers\ShowQueueMonitorController', $route->getAction('controller'));
    }

    public function testRouteCreationInNamespace()
    {
        RouteFacade::namespace('App\Http')->prefix('jobs')->group(function () {
            RouteFacade::queueMonitor();
        });

        self::assertInstanceOf(Route::class, $route = app(Router::class)->getRoutes()->getByAction(ShowQueueMonitorController::class));

        self::assertEquals('jobs', $route->uri);
        self::assertEquals('\romanzipp\QueueMonitor\Controllers\ShowQueueMonitorController', $route->getAction('controller'));
    }
}
