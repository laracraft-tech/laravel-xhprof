<?php

namespace Laravel\Spyglass\Http\Controllers;

use Laravel\Spyglass\EntryType;
use Laravel\Spyglass\Watchers\CommandWatcher;

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
