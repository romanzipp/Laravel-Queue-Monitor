<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQueueMonitorTable extends Migration
{
    public function up()
    {
        Schema::create(config('queue-monitor.table'), function (Blueprint $table) {
            $table->increments('id');

            $table->string('job_id')->index();
            $table->string('name')->nullable();
            $table->string('queue')->nullable();

            $table->timestamp('started_at')->nullable()->index();
            $table->string('started_at_exact')->nullable();

            $table->timestamp('finished_at')->nullable();
            $table->string('finished_at_exact')->nullable();

            $table->float('time_elapsed', 12, 6)->nullable()->index();

            $table->boolean('failed')->default(false)->index();

            $table->integer('attempt')->default(0);
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
