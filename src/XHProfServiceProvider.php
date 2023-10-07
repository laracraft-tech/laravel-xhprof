<?php

namespace LaracraftTech\LaravelXhprof;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use LaracraftTech\LaravelXhprof\Middleware\XHProfMiddleware;

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
        //php artisan vendor:publish --provider="LaracraftTech\LaravelXhprof\XHProfServiceProvider" --tag="config"
        $this->publishes([
            __DIR__ . '/../config/config.php' => config_path('xhprof.php'),
        ], 'config');

        $migrations = File::files(database_path('migrations'));

        // Publishing the migrations.
        //php artisan vendor:publish --provider="LaracraftTech\LaravelXhprof\XHProfServiceProvider" --tag="migrations"
        $hasMigrationCreateMigration = collect($migrations)->contains(function ($value) {
            return Str::contains($value->getFilename(), 'create_xhprof_table');
        });

        $time = time();
        if (! $hasMigrationCreateMigration) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_xhprof_table.php.stub'
                    => database_path('migrations/' . date('Y_m_d_His', $time) . '_create_xhprof_table.php'),
            ], 'migrations');
        }

        $hasMigrationAddIndexMigration = collect($migrations)->contains(function ($value) {
            return Str::contains($value->getFilename(), 'add_index_to_xhprof_table');
        });

        if (! $hasMigrationAddIndexMigration) {
            $this->publishes([
                __DIR__ . '/../database/migrations/add_index_to_xhprof_table.php.stub'
                    // make sure this migration gets a second after the one before...
                    => database_path('migrations/' . date('Y_m_d_His', $time+1) . '_add_index_to_xhprof_table.php'),
            ], 'migrations');
        }
    }
}
