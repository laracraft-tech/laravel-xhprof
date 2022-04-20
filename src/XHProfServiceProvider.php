<?php

namespace Sairahcaz\LaravelXhprof;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use Sairahcaz\LaravelXhprof\Middleware\XHProfMiddleware;

class XHProfServiceProvider extends ServiceProvider
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
        $kernel->pushMiddleware(XHProfMiddleware::class);
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        //php artisan vendor:publish --provider="Sairahcaz\LaravelXhprof\XHProfServiceProvider" --tag="config"
        $this->publishes([
            __DIR__ . '/../config/config.php' => config_path('xhprof.php'),
        ], 'config');

        // Publishing the migrations.
        //php artisan vendor:publish --provider="Sairahcaz\LaravelXhprof\XHProfServiceProvider" --tag="migrations"
        if (! class_exists('CreateXHProfTable')) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_xhprof_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_xhprof_table.php'),
                // you can add any number of migrations here
            ], 'migrations');
        }
    }
}
