<?php

namespace LaracraftTech\LaravelSpyglass\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use LaracraftTech\LaravelSpyglass\Contracts\EntriesRepository;

class MonitoredTagController extends Controller
{
    /**
     * The entry repository implementation.
     *
     * @var \LaracraftTech\LaravelSpyglass\Contracts\EntriesRepository
     */
    protected $entries;

    /**
     * Create a new controller instance.
     *
     * @param  \LaracraftTech\LaravelSpyglass\Contracts\EntriesRepository  $entries
     * @return void
     */
    public function __construct(EntriesRepository $entries)
    {
        $this->entries = $entries;
    }

    /**
     * Get all of the tags being monitored.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json([
            'tags' => $this->entries->monitoring(),
        ]);
    }

    /**
     * Begin monitoring the given tag.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function store(Request $request)
    {
        $this->entries->monitor([$request->tag]);
    }

    /**
     * Stop monitoring the given tag.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function destroy(Request $request)
    {
        $this->entries->stopMonitoring([$request->tag]);
    }
}
