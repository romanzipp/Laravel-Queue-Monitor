<?php

namespace romanzipp\QueueMonitor\Tests;

use Illuminate\Routing\Router;
use romanzipp\QueueMonitor\Enums\MonitorStatus;
use romanzipp\QueueMonitor\Models\Monitor;
use romanzipp\QueueMonitor\Tests\TestCases\DatabaseTestCase;

class EnabledUIRoutesTest extends DatabaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('queue-monitor.ui.enabled', true);
    }


    /*
     *--------------------------------------------------------------------------
     * Index
     *--------------------------------------------------------------------------
     */

    public function testIndexEnabled(): void
    {
        $this->assertTrue($this->getRouter()->has('queue-monitor::index'));
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

    public function testDeleteDisabledDeletion(): void
    {
        config([
            'queue-monitor.ui.allow_deletion' => false,
        ]);

        $this->assertTrue($this->getRouter()->has('queue-monitor::destroy'));

        /** @var \romanzipp\QueueMonitor\Models\Monitor $monitor */
        $monitor = Monitor::query()->create([
            'job_id' => mt_rand(),
        ]);

        $this
            ->delete("/jobs/monitors/{$monitor->id}")
            ->assertStatus(404);
    }

    public function testDeleteEnabled(): void
    {
        config([
            'queue-monitor.ui.allow_deletion' => true,
        ]);

        $this->assertTrue($this->getRouter()->has('queue-monitor::destroy'));

        /** @var \romanzipp\QueueMonitor\Models\Monitor $monitor */
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

    public function testPurgeDisabledPurging(): void
    {
        config([
            'queue-monitor.ui.allow_purge' => false,
        ]);

        $this->assertTrue($this->getRouter()->has('queue-monitor::purge'));

        $this
            ->delete('/jobs/purge')
            ->assertStatus(404);
    }

    public function testPurgeEnabled(): void
    {
        config([
            'queue-monitor.ui.allow_purge' => true,
        ]);

        $this->assertTrue($this->getRouter()->has('queue-monitor::purge'));

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

     public function testRetryDisabledRetrying(): void
     {
         config([
             'queue-monitor.ui.allow_retry' => false,
         ]);

         $this->assertTrue($this->getRouter()->has('queue-monitor::retry'));

         /** @var \romanzipp\QueueMonitor\Models\Monitor $monitor */
         $monitor = Monitor::query()->create([
             'job_id' => mt_rand(),
             'job_uuid' => '048f02b7-0dc2-4f9c-9baa-7852273876cc',
         ]);

         $this
             ->patch(route('queue-monitor::retry', [$monitor]))
             ->assertStatus(404);
     }

     public function testRetryEnabled(): void
     {
         config([
             'queue-monitor.ui.allow_retry' => true,
         ]);

         $this->assertTrue($this->getRouter()->has('queue-monitor::retry'));

         /** @var \romanzipp\QueueMonitor\Models\Monitor $monitor */
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

    private function getRouter(): Router
    {
        return app()->make(Router::class);
    }

}
