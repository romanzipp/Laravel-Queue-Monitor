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

        /**
         * Allow the deletion of single monitor items.
         */
        'allow_deletion' => true,

        /**
         * Allow purging all monitor entries.
         */
        'allow_purge' => true,

        'show_metrics' => true,

        /**
         * Time frame used to calculate metrics values (in days).
         */
        'metrics_time_frame' => 14,
    ],
];
