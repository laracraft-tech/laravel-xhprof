<?php

namespace Laravel\Spyglass\Http\Controllers;

use Laravel\Spyglass\EntryType;
use Laravel\Spyglass\Watchers\ScheduleWatcher;

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
