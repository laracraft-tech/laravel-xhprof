<?php

namespace Sairahcaz\LaravelXhprof\Middleware;

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
            setcookie('_profile', 1);

            require_once public_path(). '/vendor/xhprof/external/header.php';
        }

        return $next($request);
    }
}
