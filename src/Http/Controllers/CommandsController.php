<?php

namespace LaracraftTech\LaravelSpyglass\Http\Controllers;

use LaracraftTech\LaravelSpyglass\EntryType;
use LaracraftTech\LaravelSpyglass\Watchers\CommandWatcher;

class CommandsController extends EntryController
{
    /**
     * The entry type for the controller.
     *
     * @return string
     */
    protected function entryType()
    {
        return EntryType::COMMAND;
    }

    /**
     * The watcher class for the controller.
     *
     * @return string
     */
    protected function watcher()
    {
        return CommandWatcher::class;
    }
}
