<?php

namespace LaracraftTech\LaravelSpyglass\Http\Controllers;

use LaracraftTech\LaravelSpyglass\EntryType;
use LaracraftTech\LaravelSpyglass\Watchers\ScheduleWatcher;

class ScheduleController extends EntryController
{
    /**
     * The entry type for the controller.
     *
     * @return string
     */
    protected function entryType()
    {
        return EntryType::SCHEDULED_TASK;
    }

    /**
     * The watcher class for the controller.
     *
     * @return string
     */
    protected function watcher()
    {
        return ScheduleWatcher::class;
    }
}
