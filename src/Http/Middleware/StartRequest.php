<?php

namespace LaracraftTech\LaravelSpyglass\Http\Middleware;

use Closure;
use LaracraftTech\LaravelSpyglass\Events\RequestStarted;

class StartRequest
{
    public function handle($request, Closure $next)
    {
        app()['events']->dispatch(
            new RequestStarted($request)
        );

        return $next($request);
    }
}
