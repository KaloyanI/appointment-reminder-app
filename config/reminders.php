<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Reminder Retry Settings
    |--------------------------------------------------------------------------
    |
    | Configure how the system handles failed reminder retries
    |
    */
    'retries' => [
        // Maximum number of retry attempts
        'max_attempts' => env('REMINDER_MAX_RETRIES', 3),
        
        // Delay between retries (in minutes)
        // Each retry will wait (attempt * delay) minutes
        'delay_minutes' => env('REMINDER_RETRY_DELAY', 5),
        
        // Maximum delay between retries (in minutes)
        'max_delay_minutes' => env('REMINDER_MAX_RETRY_DELAY', 60),
        
        // Whether to use exponential backoff
        'use_exponential_backoff' => env('REMINDER_USE_EXPONENTIAL_BACKOFF', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Reminder Processing Settings
    |--------------------------------------------------------------------------
    */
    'processing' => [
        // How many reminders to process in one batch
        'batch_size' => env('REMINDER_BATCH_SIZE', 50),
        
        // How long to wait before marking a reminder as failed (minutes)
        'timeout_minutes' => env('REMINDER_TIMEOUT_MINUTES', 15),
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring Settings
    |--------------------------------------------------------------------------
    */
    'monitoring' => [
        // Whether to log detailed reminder processing information
        'detailed_logging' => env('REMINDER_DETAILED_LOGGING', true),
        
        // Whether to track reminder statistics
        'track_statistics' => env('REMINDER_TRACK_STATISTICS', true),
    ],
]; 