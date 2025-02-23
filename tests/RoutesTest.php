<?php

namespace romanzipp\QueueMonitor\Tests;

use romanzipp\QueueMonitor\Enums\MonitorStatus;
use romanzipp\QueueMonitor\Models\Monitor;
use romanzipp\QueueMonitor\Providers\QueueMonitorProvider;
use romanzipp\QueueMonitor\Tests\TestCases\DatabaseTestCase;

class RoutesTest extends DatabaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function defineRoutes($router)
    {
        $router->group(QueueMonitorProvider::buildRouteGroupConfig(config()), function () use (&$router) {
            require __DIR__ . '/../routes/queue-monitor.php';
        });
    }

    /*
     *--------------------------------------------------------------------------
     * Index
     *--------------------------------------------------------------------------
     */

    public function testIndexDisabled()
    {
        config(['queue-monitor.ui.enabled' => false]);

        $this
            ->get('/jobs')
            ->assertStatus(404);
    }

    public function testIndexEnabled()
    {
        config(['queue-monitor.ui.enabled' => true]);

        $this
            ->get('/jobs')
            ->assertStatus(200)
            ->assertViewIs('queue-monitor::jobs');
    }

    /*
     *--------------------------------------------------------------------------
     * Delete Monitor
     *--------------------------------------------------------------------------
     */

    public function testDeleteDisabledUi()
    {
        config(['queue-monitor.ui.enabled' => false]);

        /** @var Monitor $monitor */
        $monitor = Monitor::query()->create([
            'job_id' => mt_rand(),
        ]);

        $this
            ->delete("/jobs/monitors/{$monitor->id}")
            ->assertStatus(404);
    }

    public function testDeleteDisabledDeletion()
    {
        config([
            'queue-monitor.ui.enabled' => true,
            'queue-monitor.ui.allow_deletion' => false,
        ]);

        /** @var Monitor $monitor */
        $monitor = Monitor::query()->create([
            'job_id' => mt_rand(),
        ]);

        $this
            ->delete("/jobs/monitors/{$monitor->id}")
            ->assertStatus(404);
    }

    public function testDeleteEnabled()
    {
        config([
            'queue-monitor.ui.enabled' => true,
            'queue-monitor.ui.allow_deletion' => true,
        ]);

        /** @var Monitor $monitor */
        $monitor = Monitor::query()->create([
            'job_id' => mt_rand(),
        ]);

        $this
            ->delete("/jobs/monitors/{$monitor->id}")
            ->assertStatus(302)
            ->assertRedirectToRoute('queue-monitor::index');
    }

    /*
     *--------------------------------------------------------------------------
     * Purge
     *--------------------------------------------------------------------------
     */

    public function testPurgeDisabledUi()
    {
        config(['queue-monitor.ui.enabled' => false]);

        $this
            ->delete('/jobs/purge')
            ->assertStatus(404);
    }

    public function testPurgeDisabledPurging()
    {
        config([
            'queue-monitor.ui.enabled' => true,
            'queue-monitor.ui.allow_purge' => false,
        ]);

        $this
            ->delete('/jobs/purge')
            ->assertStatus(404);
    }

    public function testPurgeEnabled()
    {
        config([
            'queue-monitor.ui.enabled' => true,
            'queue-monitor.ui.allow_purge' => true,
        ]);

        $this
            ->delete('/jobs/purge')
            ->assertStatus(302)
            ->assertRedirectToRoute('queue-monitor::index');
    }

    /*
     *--------------------------------------------------------------------------
     * Retry monitor
     *--------------------------------------------------------------------------
     */

    public function testRetryDisabledUi()
    {
        config(['queue-monitor.ui.enabled' => false]);

        /** @var Monitor $monitor */
        $monitor = Monitor::query()->create([
            'job_id' => mt_rand(),
            'job_uuid' => '048f02b7-0dc2-4f9c-9baa-7852273876cc',
        ]);

        $this
            ->patch(route('queue-monitor::retry', [$monitor]))
            ->assertStatus(404);
    }

    public function testRetryDisabledRetrying()
    {
        config([
            'queue-monitor.ui.enabled' => true,
            'queue-monitor.ui.allow_retry' => false,
        ]);

        /** @var Monitor $monitor */
        $monitor = Monitor::query()->create([
            'job_id' => mt_rand(),
            'job_uuid' => '048f02b7-0dc2-4f9c-9baa-7852273876cc',
        ]);

        $this
            ->patch(route('queue-monitor::retry', [$monitor]))
            ->assertStatus(404);
    }

    public function testRetryEnabled()
    {
        config([
            'queue-monitor.ui.enabled' => true,
            'queue-monitor.ui.allow_retry' => true,
        ]);

        /** @var Monitor $monitor */
        $monitor = Monitor::query()->create([
            'job_id' => mt_rand(),
            'job_uuid' => '048f02b7-0dc2-4f9c-9baa-7852273876cc',
            'status' => MonitorStatus::FAILED,
            'retried' => false,
        ]);

        $this
            ->patch(route('queue-monitor::retry', [$monitor]))
            ->assertStatus(302)
            ->assertRedirectToRoute('queue-monitor::index');
    }
}
