<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateQueueMonitorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('queue_monitor', function (Blueprint $table) {
            $table->increments('id');
            $table->string('job_id')->index();
            $table->string('name')->nullable();
            $table->string('queue')->nullable();
            $table->timestamp('started_at')->nullable()->index();
            $table->timestamp('finished_at')->nullable();
            $table->integer('time_elapsed')->nullable()->index();
            $table->boolean('failed')->default(false)->index();
            $table->integer('attempt')->default(0);
            $table->text('exception')->nullable();
            $table->text('data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('queue_monitor');
    }
}
