<?php

return [
    'enable_releases_file_flag'          => env('RELEASES_FILE_FLAG_ENABLE', false),
    'enable_releases_stop_supervisor'    => env('RELEASES_STOP_SUPERVISOR_ENABLE', false),
    'sleep_interval'                     => env('RELEASES_SLEEP_INTERVAL', 1),
    'job_time_buffer'                    => env('RELEASES_JOB_TIME_BUFFER', 900),
    'job_time_shift'                     => env('RELEASES_JOB_TIME_SHIFT', 1800),
    'deployment_start_file_trigger_path' => env(
        'RELEASES_DEPLOYMENT_TRIGGER_FILE_PATH',
        storage_path('app/public/deployment.txt')
    ),
    'deployment_ready_file_trigger_path' => env(
        'RELEASES_DEPLOYMENT_READY_TRIGGER_FILE_PATH',
        storage_path('app/public/deployment_ready.txt')
    ),

];
