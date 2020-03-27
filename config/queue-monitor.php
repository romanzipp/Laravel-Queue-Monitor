<?php

return [
    /*
     * Set the table to be used for monitoring data.
     */
    'table' => 'queue_monitor',

    /*
     * Set the model used for monitoring.
     * If using a custom model, be sure to implement the
     *   romanzipp\QueueMonitor\Models\Contracts\MonitorContract
     * interface or extend the base model.
     */
    'model' => \romanzipp\QueueMonitor\Models\Monitor::class,
];
