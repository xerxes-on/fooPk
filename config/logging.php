<?php

use App\Logging\TelegramLoggerFactory;
use Carbon\Carbon;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

/**
 * Log path folder name (without trailing slash) used in different channels.
 *
 * @var string
 */
$logPathFolder = env('APP_ENV', 'production') === 'production' ?
    sprintf(
        'logs/%s_%s/%s_%s',
        strval(env('LOG_INSTANCE_TYPE', 'production')),
        strval(env('LOG_INSTANCE_ID', 'server')),
        strval(env('LOG_RELEASE_DATE', Carbon::now()->startOfWeek()->format('Ymd_Hi'))),
        strval(env('LOG_RELEASE_COMMIT', 'unknown'))
    ) :
    'logs';

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace'   => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [

        'main' => [
            'driver'            => 'stack',
            'channels'          => ['daily', 'telegram'],
            'ignore_exceptions' => false,
        ],

        'telegram' => [
            'driver'  => 'custom',
            'via'     => TelegramLoggerFactory::class,
            'api_key' => env('TELEGRAM_API_KEY'),
            'channel' => env('TELEGRAM_CHANNEL'),
            'level'   => env('TELEGRAM_LOG_LEVEL'),
        ],

        'stack' => [
            'driver'            => 'stack',
            'channels'          => ['daily'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path'   => storage_path(sprintf('%s/%s', $logPathFolder, 'laravel.log')),
            'level'  => env('LOG_LEVEL', 'debug'),
        ],

        'daily' => [
            'driver' => 'daily',
            'path'   => storage_path(sprintf('%s/%s', $logPathFolder, 'laravel.log')),
            'level'  => env('LOG_LEVEL', 'debug'),
            'days'   => 14,
        ],

        'slack' => [
            'driver'   => 'slack',
            'url'      => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji'    => ':boom:',
            'level'    => env('LOG_LEVEL', 'critical'),
        ],

        'papertrail' => [
            'driver'       => 'monolog',
            'level'        => env('LOG_LEVEL', 'debug'),
            'handler'      => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host'             => env('PAPERTRAIL_URL'),
                'port'             => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://' . env('PAPERTRAIL_URL') . ':' . env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver'    => 'monolog',
            'level'     => env('LOG_LEVEL', 'debug'),
            'handler'   => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with'      => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level'  => env('LOG_LEVEL', 'debug'),
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level'  => env('LOG_LEVEL', 'debug'),
        ],

        'null' => [
            'driver'  => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path(sprintf('%s/%s', $logPathFolder, 'laravel.log')),
        ],

        'cache' => [
            'driver' => 'single',
            'path'   => storage_path(sprintf('%s/%s', $logPathFolder, 'cache_info.log')),
        ],

        'excluded_recipes' => [
            'driver' => 'single',
            'path'   => storage_path(sprintf('%s/%s', $logPathFolder, 'excluded_recipes_info.log')),
        ],

        'shopping_list' => [
            'driver' => 'single',
            'path'   => storage_path(sprintf('%s/%s', $logPathFolder, 'shopping_list_info.log')),
        ],

        'deleted_users' => [
            'driver' => 'single',
            'path'   => storage_path(sprintf('%s/%s', $logPathFolder, 'deleted_users.log')),
        ],

        'notifications' => [
            'driver' => 'single',
            'path'   => storage_path(sprintf('%s/%s', $logPathFolder, 'notifications.log')),
        ],
    ],
];
