<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use romanzipp\QueueMonitor\Enums\MonitorStatus;
use romanzipp\QueueMonitor\Models\Monitor;

class UpdateQueueMonitorTable extends Migration
{
    public function up()
    {
        Schema::table(config('queue-monitor.table'), function (Blueprint $table) {
            $table->unsignedInteger('status')->default(MonitorStatus::RUNNING);
        });

        Monitor::query()->each(function (Monitor $monitor) {
            $monitor->status = MonitorStatus::RUNNING;

            if ($monitor->failed) {
                $monitor->status = MonitorStatus::FAILED;
            } elseif (null !== $monitor->finished_at) {
                $monitor->status = MonitorStatus::SUCCEEDED;
            }

            $monitor->save();
        });

        Schema::table(config('queue-monitor.table'), function (Blueprint $table) {
            $table->dropColumn(['failed', 'time_elapsed']);
        });
    }

    public function down()
    {
        Schema::drop(config('queue-monitor.table'));
    }
}
