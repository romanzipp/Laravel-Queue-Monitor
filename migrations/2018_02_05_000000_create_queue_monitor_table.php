<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQueueMonitorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = config('queue-monitor.table');

        Schema::create($tableName, function (Blueprint $table) {
            $table->increments('id');

            $table->string('job_id')->index();
            $table->string('name')->nullable();
            $table->string('queue')->nullable();

            $table->timestamp('started_at')->nullable()->index();
            $table->string('started_at_exact')->nullable(); // MySQL + Laravel Support for milliseconds is junky

            $table->timestamp('finished_at')->nullable();
            $table->string('finished_at_exact')->nullable(); // MySQL + Laravel Support for milliseconds is junky

            $table->float('time_elapsed', 12, 6)->nullable()->index();
            $table->boolean('failed')->default(false)->index();
            $table->integer('attempt')->default(0);
            $table->integer('progress')->nullable();
            $table->longText('exception')->nullable();
            $table->longText('data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableName = config('queue-monitor.table');

        Schema::drop($tableName);
    }
}
