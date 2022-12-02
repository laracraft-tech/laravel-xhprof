<?php

namespace LaracraftTech\LaravelSpyglass\Http\Controllers;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Routing\Controller;

class RecordingController extends Controller
{
    /**
     * The cache repository implementation.
     *
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $cache;

    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Contracts\Cache\Repository  $cache
     * @return void
     */
    public function __construct(CacheRepository $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Toggle recording.
     *
     * @return void
     */
    public function toggle()
    {
        if ($this->cache->get('spyglass:pause-recording')) {
            $this->cache->forget('spyglass:pause-recording');
        } else {
            $this->cache->put('spyglass:pause-recording', true, now()->addDays(30));
        }
    }
}
