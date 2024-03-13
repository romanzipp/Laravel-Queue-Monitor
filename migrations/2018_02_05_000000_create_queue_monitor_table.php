<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use romanzipp\QueueMonitor\Enums\MonitorStatus;

class CreateQueueMonitorTable extends Migration
{
    public function up()
    {
        Schema::create(config('queue-monitor.table'), function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('job_uuid')->nullable();

            $table->string('job_id')->index();
            $table->string('name')->nullable();
            $table->string('queue')->nullable();

            $table->unsignedInteger('status')->default(MonitorStatus::RUNNING);

            $table->dateTime('queued_at')->nullable();
            $table->timestamp('started_at')->nullable()->index();
            $table->string('started_at_exact')->nullable();

            $table->timestamp('finished_at')->nullable();
            $table->string('finished_at_exact')->nullable();

            $table->integer('attempt')->default(0);
            $table->boolean('retried')->default(false);
            $table->integer('progress')->nullable();

            $table->longText('exception')->nullable();
            $table->text('exception_message')->nullable();
            $table->text('exception_class')->nullable();

            $table->longText('data')->nullable();
        });
    }

    public function down()
    {
        Schema::drop(config('queue-monitor.table'));
    }
}
