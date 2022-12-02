<?php

namespace LaracraftTech\LaravelSpyglass\Contracts;

use Illuminate\Support\Collection;
use LaracraftTech\LaravelSpyglass\EntryResult;
use LaracraftTech\LaravelSpyglass\Storage\EntryQueryOptions;

interface EntriesRepository
{
    /**
     * Return an entry with the given ID.
     *
     * @param  mixed  $id
     * @return \LaracraftTech\LaravelSpyglass\EntryResult
     */
    public function find($id): EntryResult;

    /**
     * Return all the entries of a given type.
     *
     * @param  string|null  $type
     * @param  \LaracraftTech\LaravelSpyglass\Storage\EntryQueryOptions  $options
     * @return \Illuminate\Support\Collection|\LaracraftTech\LaravelSpyglass\EntryResult[]
     */
    public function get($type, EntryQueryOptions $options);

    /**
     * Store the given entries.
     *
     * @param  \Illuminate\Support\Collection|\LaracraftTech\LaravelSpyglass\IncomingEntry[]  $entries
     * @return void
     */
    public function store(Collection $entries);

    /**
     * Store the given entry updates.
     *
     * @param  \Illuminate\Support\Collection|\LaracraftTech\LaravelSpyglass\EntryUpdate[]  $updates
     * @return void
     */
    public function update(Collection $updates);

    /**
     * Load the monitored tags from storage.
     *
     * @return void
     */
    public function loadMonitoredTags();

    /**
     * Determine if any of the given tags are currently being monitored.
     *
     * @param  array  $tags
     * @return bool
     */
    public function isMonitoring(array $tags);

    /**
     * Get the list of tags currently being monitored.
     *
     * @return array
     */
    public function monitoring();

    /**
     * Begin monitoring the given list of tags.
     *
     * @param  array  $tags
     * @return void
     */
    public function monitor(array $tags);

    /**
     * Stop monitoring the given list of tags.
     *
     * @param  array  $tags
     * @return void
     */
    public function stopMonitoring(array $tags);
}
