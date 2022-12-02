<?php

namespace LaracraftTech\LaravelSpyglass\Http\Controllers;

use Illuminate\Routing\Controller;
use LaracraftTech\LaravelSpyglass\Contracts\ClearableRepository;

class EntriesController extends Controller
{
    /**
     * Delete all of the entries from storage.
     *
     * @param  \LaracraftTech\LaravelSpyglass\Contracts\ClearableRepository  $storage
     * @return void
     */
    public function destroy(ClearableRepository $storage)
    {
        $storage->clear();
    }
}
