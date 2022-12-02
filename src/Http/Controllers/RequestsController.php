<?php

namespace LaracraftTech\LaravelSpyglass\Http\Controllers;

use Laravel\Spyglass\EntryType;
use Laravel\Spyglass\Watchers\RequestWatcher;

class RequestsController extends EntryController
{
    /**
     * The entry type for the controller.
     *
     * @return string
     */
    protected function entryType()
    {
        return EntryType::REQUEST;
    }

    /**
     * The watcher class for the controller.
     *
     * @return string
     */
    protected function watcher()
    {
        return RequestWatcher::class;
    }
}
