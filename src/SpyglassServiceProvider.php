<?php

namespace LaracraftTech\LaravelSpyglass;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use LaracraftTech\LaravelSpyglass\Middleware\SpyglassMiddleware;

class SpyglassServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }

        $kernel = $this->app[Kernel::class];
        $kernel->pushMiddleware(SpyglassMiddleware::class);
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        //php artisan vendor:publish --provider="LaracraftTech\LaravelSpyglass\SpyglassServiceProvider" --tag="config"
        $this->publishes([
            __DIR__ . '/../config/config.php' => config_path('spyglass.php'),
        ], 'config');

        // Publishing the migrations.
        //php artisan vendor:publish --provider="LaracraftTech\LaravelSpyglass\SpyglassServiceProvider" --tag="migrations"
        if (! class_exists('CreateSpyglassTable')) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_spyglass_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_spyglass_table.php'),
                // you can add any number of migrations here
            ], 'migrations');
        }
    }
}
