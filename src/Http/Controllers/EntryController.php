<?php

namespace LaracraftTech\LaravelSpyglass\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use LaracraftTech\LaravelSpyglass\Contracts\EntriesRepository;
use LaracraftTech\LaravelSpyglass\Storage\EntryQueryOptions;

abstract class EntryController extends Controller
{
    /**
     * The entry type for the controller.
     *
     * @return string
     */
    abstract protected function entryType();

    /**
     * The watcher class for the controller.
     *
     * @return string
     */
    abstract protected function watcher();

    /**
     * List the entries of the given type.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \LaracraftTech\LaravelSpyglass\Contracts\EntriesRepository  $storage
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, EntriesRepository $storage)
    {
        return response()->json([
            'entries' => $storage->get(
                $this->entryType(),
                EntryQueryOptions::fromRequest($request)->exclude(['prof_data'])
            ),
            'status' => $this->status(),
        ]);
    }

    /**
     * Get an entry with the given ID.
     *
     * @param  \LaracraftTech\LaravelSpyglass\Contracts\EntriesRepository  $storage
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(EntriesRepository $storage, $id)
    {
        $entry = $storage->find($id)->generateAvatar();

        return response()->json([
            'entry' => $entry,
//            'batch' => $storage->get(null, EntryQueryOptions::forBatchId($entry->batchId)->limit(-1)),
        ]);
    }

    /**
     * Determine the watcher recording status.
     *
     * @return string
     */
    protected function status()
    {
        if (! config('spyglass.enabled', false)) {
            return 'disabled';
        }

        if (cache('spyglass:pause-recording', false)) {
            return 'paused';
        }

        $watcher = config('spyglass.watchers.'.$this->watcher());

        if (! $watcher || (isset($watcher['enabled']) && ! $watcher['enabled'])) {
            return 'off';
        }

        return 'enabled';
    }
}
