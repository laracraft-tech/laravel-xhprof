<?php

namespace Laravel\Spyglass\Http\Controllers;

use Illuminate\Routing\Controller;
use Laravel\Spyglass\Contracts\ClearableRepository;

class EntriesController extends Controller
{
    /**
     * Delete all of the entries from storage.
     *
     * @param  \Laravel\Spyglass\Contracts\ClearableRepository  $storage
     * @return void
     */
    public function destroy(ClearableRepository $storage)
    {
        $storage->clear();
    }
}
