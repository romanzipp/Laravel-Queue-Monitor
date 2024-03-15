<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJobUuidAndRetriedToQueueMonitorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(config('queue-monitor.table'), function (Blueprint $table) {
            $table->uuid('job_uuid')->nullable()->after('id');
            $table->boolean('retried')->default(false)->after('attempt');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(config('queue-monitor.table'), function (Blueprint $table) {
            $table->dropColumn([
                'job_uuid',
                'retried',
            ]);
        });
    }
}
