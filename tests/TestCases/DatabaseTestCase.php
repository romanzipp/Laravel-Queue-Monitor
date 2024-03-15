<?php

namespace romanzipp\QueueMonitor\Tests\TestCases;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

abstract class DatabaseTestCase extends TestCase
{
    use RefreshDatabase {
        refreshTestDatabase as baseRefreshTestDatabase;
        refreshInMemoryDatabase as baseRefreshInMemoryDatabase;
    }

    protected function refreshInMemoryDatabase(): void
    {
        $this->createJobsTable();
        $this->createFailedJobsTable();

        $this->baseRefreshInMemoryDatabase();
    }

    protected function refreshTestDatabase(): void
    {
        $this->tryCreateJobsTables();

        $this->baseRefreshTestDatabase();
    }

    protected function tryCreateJobsTables(): void
    {
        if ( ! $this->app['db']->connection()->getSchemaBuilder()->hasTable('jobs')) {
            $this->createJobsTable();
        }

        if ( ! $this->app['db']->connection()->getSchemaBuilder()->hasTable('failed_jobs')) {
            $this->createFailedJobsTable();
        }
    }

    private function createJobsTable(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });
    }

    private function createFailedJobsTable(): void
    {
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }
}
