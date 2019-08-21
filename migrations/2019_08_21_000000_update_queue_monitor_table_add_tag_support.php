<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateQueueMonitorTableAddTagSupport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = config('queue-monitor.table');

        Schema::table($tableName, function (Blueprint $table) {
            $table->longText('tags')->nullable();
            $table->index('queue', 'queue_index');
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

        Schema::table($tableName, function (Blueprint $table) {
            $table->dropColumn('tags');
            $table->dropIndex('queue_index');
        });
    }
}
