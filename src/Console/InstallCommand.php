<?php

namespace LaracraftTech\LaravelSpyglass\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spyglass:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install all of the Spyglass resources';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->comment('Publishing Spyglass Service Provider...');
        $this->callSilent('vendor:publish', ['--tag' => 'spyglass-provider']);

        $this->comment('Publishing Spyglass Assets...');
        $this->callSilent('vendor:publish', ['--tag' => 'spyglass-assets']);

        $this->comment('Publishing Spyglass Configuration...');
        $this->callSilent('vendor:publish', ['--tag' => 'spyglass-config']);

        $this->registerSpyglassServiceProvider();

        $this->info('Spyglass scaffolding installed successfully.');
    }

    /**
     * Register the Spyglass service provider in the application configuration file.
     *
     * @return void
     */
    protected function registerSpyglassServiceProvider()
    {
        $namespace = Str::replaceLast('\\', '', $this->laravel->getNamespace());

        $appConfig = file_get_contents(config_path('app.php'));

        if (Str::contains($appConfig, $namespace.'\\Providers\\SpyglassServiceProvider::class')) {
            return;
        }

        $lineEndingCount = [
            "\r\n" => substr_count($appConfig, "\r\n"),
            "\r" => substr_count($appConfig, "\r"),
            "\n" => substr_count($appConfig, "\n"),
        ];

        $eol = array_keys($lineEndingCount, max($lineEndingCount))[0];

        file_put_contents(config_path('app.php'), str_replace(
            "{$namespace}\\Providers\RouteServiceProvider::class,".$eol,
            "{$namespace}\\Providers\RouteServiceProvider::class,".$eol."        {$namespace}\Providers\SpyglassServiceProvider::class,".$eol,
            $appConfig
        ));

        file_put_contents(app_path('Providers/SpyglassServiceProvider.php'), str_replace(
            "namespace App\Providers;",
            "namespace {$namespace}\Providers;",
            file_get_contents(app_path('Providers/SpyglassServiceProvider.php'))
        ));
    }
}
