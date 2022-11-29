<?php

namespace LaracraftTech\LaravelSpyglass;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use LaracraftTech\LaravelSpyglass\Http\Middleware\SpyglassMiddleware;

class SpyglassServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPublishing();

        if (! config('spyglass.enabled')) {
            return;
        }

        Route::middlewareGroup('spyglass', config('spyglass.middleware', []));

        $this->registerRoutes();
        $this->registerMigrations();

        $kernel = $this->app[Kernel::class];
        $kernel->prependMiddleware(SpyglassMiddleware::class);
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
     * Get the Telescope route group configuration array.
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

//            $this->publishes([
//                __DIR__.'/../public' => public_path('vendor/spyglass'),
//            ], ['spyglass-assets', 'laravel-assets']);

            $this->publishes([
                __DIR__.'/../config/spyglass.php' => config_path('spyglass.php'),
            ], 'spyglass-config');

//            $this->publishes([
//                __DIR__.'/../stubs/SpyglassServiceProvider.stub' => app_path('Providers/SpyglassServiceProvider.php'),
//            ], 'spyglass-provider');
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

//        $this->registerStorageDriver();
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
