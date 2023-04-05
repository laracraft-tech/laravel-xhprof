<?php

namespace LaracraftTech\LaravelXhprof\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;

class XHProfMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws Exception
     */
    public function handle(Request $request, Closure $next)
    {
        if (config('xhprof.enabled')) {
            if (!extension_loaded('xhprof')) {
                throw new Exception('xhprof is enabled but extension is not installed or disabled! Please install or enable xhprof extension!');
            }

            //this needs to be declared as global!
            global $_xhprof;

            //if profiler is enabled in config, don't wait for ?_profile=1 get parameter (which sets the cookie)
            //to start profiling, just enable it immediately
            $_COOKIE['_profile'] = 1;

            // Only include if request does not expect JSON.
            // Not using $request->excpectsJson or $rquest->wantsJson to work with Livewire because Livewire only set content-type header to json.
            if ($request->header('content-type') !== 'application/json') {
                require_once public_path(). '/vendor/xhprof/external/header.php';
            }
        }

        return $next($request);
    }
}
