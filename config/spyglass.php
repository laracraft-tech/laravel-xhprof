<?php

use LaracraftTech\LaravelSpyglass\Http\Middleware\Authorize;
use LaracraftTech\LaravelSpyglass\Watchers;

return [

    /*
    |--------------------------------------------------------------------------
    | Spyglass Domain
    |--------------------------------------------------------------------------
    |
    | This is the subdomain where Spyglass will be accessible from. If the
    | setting is null, Spyglass will reside under the same domain as the
    | application. Otherwise, this value will be used as the subdomain.
    |
    */

    'domain' => env('SPYGLASS_DOMAIN', null),

    /*
    |--------------------------------------------------------------------------
    | Spyglass Path
    |--------------------------------------------------------------------------
    |
    | This is the URI path where Spyglass will be accessible from. Feel free
    | to change this path to anything you like. Note that the URI will not
    | affect the paths of its internal API that aren't exposed to users.
    |
    */

    'path' => env('SPYGLASS_PATH', 'spyglass'),

    /*
    |--------------------------------------------------------------------------
    | Spyglass Storage Driver
    |--------------------------------------------------------------------------
    |
    | This configuration options determines the storage driver that will
    | be used to store Spyglass's data. In addition, you may set any
    | custom options as needed by the particular driver you choose.
    |
    */

    'driver' => env('SPYGLASS_DRIVER', 'database'),

    'storage' => [
        'database' => [
            'connection' => env('DB_CONNECTION', 'mysql'),
            'chunk' => 1000,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Spyglass Master Switch
    |--------------------------------------------------------------------------
    |
    | This option may be used to disable all Spyglass watchers regardless
    | of their individual configuration, which simply provides a single
    | and convenient way to enable or disable Spyglass data storage.
    |
    */

    'enabled' => env('SPYGLASS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Spyglass Route Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will be assigned to every Spyglass route, giving you
    | the chance to add your own middleware to this list or change any of
    | the existing middleware. Or, you can simply stick with this list.
    |
    */

    'middleware' => [
        'web',
        Authorize::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed / Ignored Paths & Commands
    |--------------------------------------------------------------------------
    |
    | The following array lists the URI paths, Artisan commands or functions that
    | will not be profiled by Spyglass. In addition to this list, some Laravel
    | commands, like migrations and queue commands, are always ignored.
    |
    */

    'only_paths' => [
        // 'api/*'
    ],

    'ignore_paths' => [
        'nova-api*',
    ],

    'ignore_commands' => [
        //
    ],

    'ignore_functions' => [
        //
    ],


    /*
    |--------------------------------------------------------------------------
    | Spyglass PHP Extension Name
    |--------------------------------------------------------------------------
    |
    | Currently supported: xhprof, tideways, tideways_xhprof
    | Be sure the extension is installed on your server and enable!
    |
    */
    'extension_name' => env('SPYGLASS_PHP_EXTENSION', 'xhprof'),

    'extension_flags' => [
        'cpu',
        'memory',
        //'no_builtins', by default builtins are enabled,
    ],

    /*
    |--------------------------------------------------------------------------
    | Spyglass Serializer (MySQL/MySQLi/PDO ONLY)
    |--------------------------------------------------------------------------
    |
    | Switch to "json" for better performance and support for larger profiler data sets.
    | WARNING: Will break with existing profile data, you will need to TRUNCATE the
    | profile data table.
    |
    */
    'serializer' => env('SPYGLASS_SERIALIZER', 'php'),

    /*
    |--------------------------------------------------------------------------
    | Spyglass Compress Level
    |--------------------------------------------------------------------------
    |
    | The value of 2 seems to be light enough that we're not killing the server,
    | but still gives us lots of breathing room on full production code.
    |
    */
    'compress_level' => env('SPYGLASS_COMP_LEVEL', 2),

    /*
    |--------------------------------------------------------------------------
    | Spyglass Watchers
    |--------------------------------------------------------------------------
    |
    | The following array lists the "watchers" that will be registered with
    | Spyglass. The watchers gather the application's profile data when
    | a request or task is executed. Feel free to customize this list.
    |
    */

    'watchers' => [
        Watchers\CommandWatcher::class => [
            'enabled' => env('SPYGLASS_COMMAND_WATCHER', true),
            'ignore' => [],
        ],

        Watchers\JobWatcher::class => env('SPYGLASS_JOB_WATCHER', true),

        Watchers\RequestWatcher::class => [
            'enabled' => env('SPYGLASS_REQUEST_WATCHER', true),
            'size_limit' => env('SPYGLASS_RESPONSE_SIZE_LIMIT', 64),
            'ignore_http_methods' => [],
            'ignore_status_codes' => [],
        ],

        Watchers\ScheduleWatcher::class => env('SPYGLASS_SCHEDULE_WATCHER', true),
    ],
];
