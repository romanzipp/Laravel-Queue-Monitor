<?php

return [
    // Set the table to be used for monitoring data.
    'table' => 'queue_monitor',
    'connection' => null,

    /*
     * Set the model used for monitoring.
     * If using a custom model, be sure to implement the
     *   romanzipp\QueueMonitor\Models\Contracts\MonitorContract
     * interface or extend the base model.
     */
    'model' => romanzipp\QueueMonitor\Models\Monitor::class,

    // Determined if the queued jobs should be monitored
    'monitor_queued_jobs' => true,

    // Specify the max character length to use for storing exception backtraces.
    'db_max_length_exception' => 4294967295,
    'db_max_length_exception_message' => 65535,

    // The optional UI settings.
    'ui' => [
        // Enable the UI
        'enabled' => false,

        // Accepts route group configuration
        'route' => [
            'prefix' => 'jobs',
            // 'middleware' => ''
        ],

        // Set the monitored jobs count to be displayed per page.
        'per_page' => 35,

        // Show custom data stored on model
        'show_custom_data' => false,

        // Allow the deletion of single monitor items.
        'allow_deletion' => true,

        // Allow retry for a single failed monitor item.
        'allow_retry' => true,

        // Allow purging all monitor entries.
        'allow_purge' => true,

        'show_metrics' => true,

        // Time frame used to calculate metrics values (in days).
        'metrics_time_frame' => 14,

        // The interval before refreshing the dashboard (in seconds).
        'refresh_interval' => null,
    ],
];
