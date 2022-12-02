<?php

namespace LaracraftTech\LaravelSpyglass;

use Closure;
use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Testing\Fakes\EventFake;
use LaracraftTech\LaravelSpyglass\Contracts\EntriesRepository;
use LaracraftTech\LaravelSpyglass\Contracts\TerminableRepository;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;

class Spyglass
{
    use AuthorizesRequests,
        ListensForStorageOpportunities,
        RegistersWatchers;

    /**
     * The callbacks that filter the entries that should be recorded.
     *
     * @var array
     */
    public static $filterUsing = [];

    /**
     * The callback executed after queuing a new entry.
     *
     * @var \Closure
     */
    public static $afterRecordingHook;

    /**
     * The callbacks executed after storing the entries.
     *
     * @var \Closure
     */
    public static $afterStoringHooks = [];

    /**
     * The list of queued entries to be stored.
     *
     * @var array
     */
    public static $entriesQueue = [];

    /**
     * The list of hidden request headers.
     *
     * @var array
     */
    public static $hiddenRequestHeaders = [
        'authorization',
        'php-auth-pw',
    ];

    /**
     * The list of hidden request parameters.
     *
     * @var array
     */
    public static $hiddenRequestParameters = [
        'password',
        'password_confirmation',
    ];

    /**
     * The list of hidden response parameters.
     *
     * @var array
     */
    public static $hiddenResponseParameters = [];

    /**
     * Indicates if Spyglass should ignore events fired by Laravel.
     *
     * @var bool
     */
    public static $ignoreFrameworkEvents = true;

    /**
     * Indicates if Spyglass should use the dark theme.
     *
     * @var bool
     */
    public static $useDarkTheme = false;

    /**
     * Indicates if Spyglass should record entries.
     *
     * @var bool
     */
    public static $shouldRecord = false;

    /**
     * Indicates if Spyglass migrations will be run.
     *
     * @var bool
     */
    public static $runsMigrations = true;

    /**
     * Register the Spyglass watchers and start recording if necessary.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    public static function start($app)
    {
        if (! config('spyglass.enabled')) {
            return;
        }

        static::registerWatchers($app);

        if (! static::runningWithinOctane($app) &&
            (static::runningApprovedArtisanCommand($app) ||
                static::handlingApprovedRequest($app))
        ) {
            static::startRecording($loadMonitoredTags = false);
        }
    }

    /**
     * Determine if Spyglass is running within Octane.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return bool
     */
    protected static function runningWithinOctane($app)
    {
        return isset($_SERVER['LARAVEL_OCTANE']);
    }

    /**
     * Determine if the application is running an approved command.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return bool
     */
    protected static function runningApprovedArtisanCommand($app)
    {
        return $app->runningInConsole() && ! in_array(
                $_SERVER['argv'][1] ?? null,
                array_merge([
                    // 'migrate',
                    'migrate:rollback',
                    'migrate:fresh',
                    // 'migrate:refresh',
                    'migrate:reset',
                    'migrate:install',
                    'package:discover',
                    'queue:listen',
                    'queue:work',
                    'horizon',
                    'horizon:work',
                    'horizon:supervisor',
                ], config('spyglass.ignoreCommands', []), config('spyglass.ignore_commands', []))
            );
    }

    /**
     * Determine if the application is handling an approved request.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return bool
     */
    protected static function handlingApprovedRequest($app)
    {
        if ($app->runningInConsole()) {
            return false;
        }

        return static::requestIsToApprovedDomain($app['request']) &&
            static::requestIsToApprovedUri($app['request']);
    }

    /**
     * Determine if the request is to an approved domain.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected static function requestIsToApprovedDomain($request): bool
    {
        return is_null(config('spyglass.domain')) ||
            config('spyglass.domain') !== $request->getHost();
    }

    /**
     * Determine if the request is to an approved URI.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected static function requestIsToApprovedUri($request)
    {
        if (! empty($only = config('spyglass.only_paths', []))) {
            return $request->is($only);
        }

        return ! $request->is(
            collect([
                'spyglass-api*',
                'vendor/spyglass*',
                'spyglass-api*',
                'vendor/spyglass*',
                (config('horizon.path') ?? 'horizon').'*',
                'vendor/horizon*',
            ])
                ->merge(config('spyglass.ignore_paths', []))
                ->unless(is_null(config('spyglass.path')), function ($paths) {
                    return $paths->prepend(config('spyglass.path').'*');
                })
                ->all()
        );
    }

    /**
     * Start recording entries.
     *
     * @param  bool  $loadMonitoredTags
     * @return void
     */
    public static function startRecording($loadMonitoredTags = true)
    {
        if ($loadMonitoredTags) {
            app(EntriesRepository::class)->loadMonitoredTags();
        }

        static::$shouldRecord = ! cache('spyglass:pause-recording');
    }

    /**
     * Stop recording entries.
     *
     * @return void
     */
    public static function stopRecording()
    {
        static::$shouldRecord = false;
    }

    /**
     * Execute the given callback without recording Spyglass entries.
     *
     * @param  callable  $callback
     * @return void
     */
    public static function withoutRecording($callback)
    {
        $shouldRecord = static::$shouldRecord;

        static::$shouldRecord = false;

        try {
            call_user_func($callback);
        } finally {
            static::$shouldRecord = $shouldRecord;
        }
    }

    /**
     * Determine if Spyglass is recording.
     *
     * @return bool
     */
    public static function isRecording()
    {
        return static::$shouldRecord && ! app('events') instanceof EventFake;
    }

    /**
     * Record the given entry.
     *
     * @param  string  $type
     * @param  \LaracraftTech\LaravelSpyglass\IncomingEntry  $entry
     * @return void
     */
    protected static function record(string $type, IncomingEntry $entry)
    {
        if (! static::isRecording()) {
            return;
        }

        try {
            if (Auth::hasResolvedGuards() && Auth::hasUser()) {
                $entry->user(Auth::user());
            }
        } catch (Throwable $e) {
            // Do nothing.
        }

        $entry->type($type);

        static::withoutRecording(function () use ($entry) {
            if (collect(static::$filterUsing)->every->__invoke($entry)) {
                static::$entriesQueue[] = $entry;
            }

            if (static::$afterRecordingHook) {
                call_user_func(static::$afterRecordingHook, new static, $entry);
            }
        });
    }

    /**
     * Record the given entry.
     *
     * @param  \LaracraftTech\LaravelSpyglass\IncomingEntry  $entry
     * @return void
     */
    public static function recordCommand(IncomingEntry $entry)
    {
        static::record(EntryType::COMMAND, $entry);
    }

    /**
     * Record the given entry.
     *
     * @param  \LaracraftTech\LaravelSpyglass\IncomingEntry  $entry
     * @return void
     */
    public static function recordJob($entry)
    {
        static::record(EntryType::JOB, $entry);
    }

    /**
     * Record the given entry.
     *
     * @param  \LaracraftTech\LaravelSpyglass\IncomingEntry  $entry
     * @return void
     */
    public static function recordRequest(IncomingEntry $entry)
    {
        static::record(EntryType::REQUEST, $entry);
    }

    /**
     * Record the given entry.
     *
     * @param  \LaracraftTech\LaravelSpyglass\IncomingEntry  $entry
     * @return void
     */
    public static function recordScheduledCommand(IncomingEntry $entry)
    {
        static::record(EntryType::SCHEDULED_TASK, $entry);
    }

    /**
     * Flush all entries in the queue.
     *
     * @return static
     */
    public static function flushEntries()
    {
        static::$entriesQueue = [];

        return new static;
    }

    /**
     * Record the given exception.
     *
     * @param  \Throwable|\Exception  $e
     * @param  array  $tags
     * @return void
     */
    public static function catch($e, $tags = [])
    {
        if ($e instanceof Throwable && ! $e instanceof Exception) {
            $e = new FatalThrowableError($e);
        }

        event(new MessageLogged('error', $e->getMessage(), [
            'exception' => $e,
            'spyglass' => $tags,
        ]));
    }

    /**
     * Set the callback that filters the entries that should be recorded.
     *
     * @param  \Closure  $callback
     * @return static
     */
    public static function filter(Closure $callback)
    {
        static::$filterUsing[] = $callback;

        return new static;
    }

    /**
     * Set the callback that will be executed after an entry is recorded in the queue.
     *
     * @param  \Closure  $callback
     * @return static
     */
    public static function afterRecording(Closure $callback)
    {
        static::$afterRecordingHook = $callback;

        return new static;
    }

    /**
     * Add a callback that will be executed after an entry is stored.
     *
     * @param  \Closure  $callback
     * @return static
     */
    public static function afterStoring(Closure $callback)
    {
        static::$afterStoringHooks[] = $callback;

        return new static;
    }

    /**
     * Store the queued entries and flush the queue.
     *
     * @param  \LaracraftTech\LaravelSpyglass\Contracts\EntriesRepository  $storage
     * @return void
     */
    public static function store(EntriesRepository $storage)
    {
        if (empty(static::$entriesQueue)) {
            return;
        }

        static::withoutRecording(function () use ($storage) {
            try {
                $storage->store(collect(static::$entriesQueue));

                if ($storage instanceof TerminableRepository) {
                    $storage->terminate();
                }

                collect(static::$afterStoringHooks)->every->__invoke(static::$entriesQueue);
            } catch (Throwable $e) {
                app(ExceptionHandler::class)->report($e);
            }
        });

        static::$entriesQueue = [];
    }

    /**
     * Hide the given request header.
     *
     * @param  array  $headers
     * @return static
     */
    public static function hideRequestHeaders(array $headers)
    {
        static::$hiddenRequestHeaders = array_merge(
            static::$hiddenRequestHeaders,
            $headers
        );

        return new static;
    }

    /**
     * Hide the given request parameters.
     *
     * @param  array  $attributes
     * @return static
     */
    public static function hideRequestParameters(array $attributes)
    {
        static::$hiddenRequestParameters = array_merge(
            static::$hiddenRequestParameters,
            $attributes
        );

        return new static;
    }

    /**
     * Hide the given response parameters.
     *
     * @param  array  $attributes
     * @return static
     */
    public static function hideResponseParameters(array $attributes)
    {
        static::$hiddenResponseParameters = array_merge(
            static::$hiddenResponseParameters,
            $attributes
        );

        return new static;
    }

    /**
     * Specifies that Spyglass should record events fired by Laravel.
     *
     * @return static
     */
    public static function recordFrameworkEvents()
    {
        static::$ignoreFrameworkEvents = false;

        return new static;
    }

    /**
     * Specifies that Spyglass should use the dark theme.
     *
     * @return static
     */
    public static function night()
    {
        static::$useDarkTheme = true;

        return new static;
    }

    /**
     * Register the Spyglass user avatar callback.
     *
     * @param  \Closure  $callback
     * @return static
     */
    public static function avatar(Closure $callback)
    {
        Avatar::register($callback);

        return new static;
    }

    /**
     * Get the default JavaScript variables for Spyglass.
     *
     * @return array
     */
    public static function scriptVariables()
    {
        return [
            'path' => config('spyglass.path'),
            'timezone' => config('app.timezone'),
            'recording' => ! cache('spyglass:pause-recording'),
        ];
    }

    /**
     * Configure Spyglass to not register its migrations.
     *
     * @return static
     */
    public static function ignoreMigrations()
    {
        static::$runsMigrations = false;

        return new static;
    }
}
