<?php

namespace romanzipp\QueueMonitor\Tests;

use Illuminate\Routing\Router;
use romanzipp\QueueMonitor\Tests\TestCases\DatabaseTestCase;

class DisabledUIRoutesTest extends DatabaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('queue-monitor.ui.enabled', false);
    }

    public function testIndexDisabled(): void
    {
        $this->assertFalse($this->getRouter()->has('queue-monitor::index'));
    }

    public function testDestroyDisabled(): void
    {
        $this->assertFalse($this->getRouter()->has('queue-monitor::destroy'));
    }

    public function testRetryDisabled(): void
    {
        $this->assertFalse($this->getRouter()->has('queue-monitor::retry'));
    }

    public function testPurgeDisabled(): void
    {
        $this->assertFalse($this->getRouter()->has('queue-monitor::purge'));
    }

    private function getRouter(): Router
    {
        return app()->make(Router::class);
    }

}
