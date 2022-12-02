<?php

namespace LaracraftTech\LaravelSpyglass;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use LaracraftTech\LaravelSpyglass\Contracts\ClearableRepository;
use LaracraftTech\LaravelSpyglass\Contracts\EntriesRepository;
use LaracraftTech\LaravelSpyglass\Contracts\PrunableRepository;
use LaracraftTech\LaravelSpyglass\Http\Middleware\StartRequest;
use LaracraftTech\LaravelSpyglass\Storage\DatabaseEntriesRepository;

class SpyglassServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerCommands();
        $this->registerPublishing();

        if (! config('spyglass.enabled')) {
            return;
        }

        Route::middlewareGroup('spyglass', config('spyglass.middleware', []));

        $this->registerRoutes();
        $this->registerMigrations();

        $kernel = $this->app[Kernel::class];
        $kernel->prependMiddleware(StartRequest::class);

        Spyglass::start($this->app);
        Spyglass::listenForStorageOpportunities($this->app);

        $this->loadViewsFrom(
            __DIR__.'/../resources/views', 'spyglass'
        );
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    private function registerRoutes()
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__.'/Http/routes.php');
        });
    }

    /**
     * Get the Spyglass route group configuration array.
     *
     * @return array
     */
    private function routeConfiguration()
    {
        return [
            'domain' => config('spyglass.domain', null),
            'namespace' => 'LaracraftTech\LaravelSpyglass\Http\Controllers',
            'prefix' => config('spyglass.path'),
            'middleware' => 'spyglass',
        ];
    }

    /**
     * Register the package's migrations.
     *
     * @return void
     */
    private function registerMigrations()
    {
        if ($this->app->runningInConsole() && $this->shouldMigrate()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    private function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'spyglass-migrations');

            $this->publishes([
                __DIR__.'/../public' => public_path('vendor/spyglass'),
            ], ['spyglass-assets', 'laravel-assets']);

            $this->publishes([
                __DIR__.'/../config/spyglass.php' => config_path('spyglass.php'),
            ], 'spyglass-config');

            $this->publishes([
                __DIR__.'/../stubs/SpyglassServiceProvider.stub' => app_path('Providers/SpyglassServiceProvider.php'),
            ], 'spyglass-provider');
        }
    }

    /**
     * Register the package's commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\ClearCommand::class,
                Console\InstallCommand::class,
                Console\PauseCommand::class,
                Console\PruneCommand::class,
                Console\PublishCommand::class,
                Console\ResumeCommand::class,
            ]);
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/spyglass.php', 'spyglass'
        );

        $this->app->bind(XHProf::class, function() {
            return new XHProf(
                config('spyglass.extension_name'),
                config('spyglass.extension_flags'),
                config('spyglass.ignore_functions'),
            );
        });

        $this->registerStorageDriver();
    }

    /**
     * Register the package storage driver.
     *
     * @return void
     */
    protected function registerStorageDriver()
    {
        $driver = config('spyglass.driver');

        if (method_exists($this, $method = 'register'.ucfirst($driver).'Driver')) {
            $this->$method();
        }
    }

    /**
     * Register the package database storage driver.
     *
     * @return void
     */
    protected function registerDatabaseDriver()
    {
        $this->app->singleton(
            EntriesRepository::class, DatabaseEntriesRepository::class
        );

        $this->app->singleton(
            ClearableRepository::class, DatabaseEntriesRepository::class
        );

        $this->app->singleton(
            PrunableRepository::class, DatabaseEntriesRepository::class
        );

        $this->app->when(DatabaseEntriesRepository::class)
            ->needs('$connection')
            ->give(config('spyglass.storage.database.connection'));

        $this->app->when(DatabaseEntriesRepository::class)
            ->needs('$chunkSize')
            ->give(config('spyglass.storage.database.chunk'));
    }

    /**
     * Determine if we should register the migrations.
     *
     * @return bool
     */
    protected function shouldMigrate()
    {
        return Spyglass::$runsMigrations && config('spyglass.driver') === 'database';
    }
}
