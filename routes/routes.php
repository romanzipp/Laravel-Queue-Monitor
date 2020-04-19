<?php

use Illuminate\Support\Facades\Route;

Route::prefix(config('queue-monitor.ui.route'))->group(static function () {

    Route::get('', \romanzipp\QueueMonitor\Controllers\ShowQueueMonitorController::class);

});
