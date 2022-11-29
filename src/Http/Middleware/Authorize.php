<?php

namespace LaracraftTech\LaravelSpyglass\Http\Middleware;

use LaracraftTech\LaravelSpyglass\Spyglass;

class Authorize
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     */
    public function handle($request, $next)
    {
        return Spyglass::check($request) ? $next($request) : abort(403);
    }
}
