<?php

namespace romanzipp\QueueMonitor\Tests;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as BaseTestCase;
use romanzipp\QueueMonitor\Providers\QueueMonitorProvider;
use romanzipp\QueueMonitor\Tests\Support\BaseJob;

class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->withoutMockingConsoleOutput();
        $this->withoutExceptionHandling();
    }

    public function refreshInMemoryDatabase()
    {
        throw new \RuntimeException('Only supporting MySQL');
    }

    protected function afterRefreshingDatabase()
    {
        if ( ! $this->app['db']->connection()->getSchemaBuilder()->hasTable('jobs')) {
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

        if ( ! $this->app['db']->connection()->getSchemaBuilder()->hasTable('failed_jobs')) {
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

    protected function dispatch(BaseJob $job): self
    {
        app(Dispatcher::class)->dispatch($job);

        $this->assertQueueSize(1);

        return $this;
    }

    protected function assertDispatched(string $jobClass): self
    {
        $rows = DB::select('SELECT * FROM jobs');

        self::assertCount(1, $rows);
        self::assertEquals($jobClass, json_decode($rows[0]->payload)->displayName);

        return $this;
    }

    protected function assertQueueSize(int $size): self
    {
        self::assertSame($size, $this->app['queue']->connection()->size());

        return $this;
    }

    protected function workQueue(): void
    {
        $job = $this->app['queue']->connection($this->app['config']['queue.default']);

        if (null === $job) {
            self::fail('No job dispatched');
        }

        $this->artisan('queue:work --once');
    }

    protected function getPackageProviders($app)
    {
        return [
            QueueMonitorProvider::class,
        ];
    }
}
