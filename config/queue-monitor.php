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

    /*
     * The optional UI settings.
     */
    'ui' => [
        /*
         * Set the monitored jobs count to be displayed per page.
         */
        'per_page' => 35,

        /*
         *  Show custom data stored on model
         */
        'show_custom_data' => false,
    ],
];
