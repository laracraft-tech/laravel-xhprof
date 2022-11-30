<?php

namespace LaracraftTech\LaravelSpyglass;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class SpyglassApplicationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorization();
    }

    /**
     * Configure the Spyglass authorization services.
     *
     * @return void
     */
    protected function authorization()
    {
        $this->gate();

        Spyglass::auth(function ($request) {
            return app()->environment('local') ||
                   Gate::check('viewSpyglass', [$request->user()]);
        });
    }

    /**
     * Register the Spyglass gate.
     *
     * This gate determines who can access Spyglass in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewSpyglass', function ($user) {
            return in_array($user->email, [
                //
            ]);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
