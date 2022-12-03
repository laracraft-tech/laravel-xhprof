<?php

namespace LaracraftTech\LaravelSpyglass\Storage;

use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LaracraftTech\LaravelSpyglass\Contracts\ClearableRepository;
use LaracraftTech\LaravelSpyglass\Contracts\EntriesRepository as Contract;
use LaracraftTech\LaravelSpyglass\Contracts\PrunableRepository;
use LaracraftTech\LaravelSpyglass\Contracts\TerminableRepository;
use LaracraftTech\LaravelSpyglass\EntryResult;

class DatabaseEntriesRepository implements Contract, ClearableRepository, PrunableRepository, TerminableRepository
{
    /**
     * The database connection name that should be used.
     *
     * @var string
     */
    protected $connection;

    /**
     * The number of entries that will be inserted at once into the database.
     *
     * @var int
     */
    protected $chunkSize = 1000;

    /**
     * The tags currently being monitored.
     *
     * @var array|null
     */
    protected $monitoredTags;

    /**
     * Create a new database repository.
     *
     * @param  string  $connection
     * @param  int  $chunkSize
     * @return void
     */
    public function __construct(string $connection, int $chunkSize = null)
    {
        $this->connection = $connection;

        if ($chunkSize) {
            $this->chunkSize = $chunkSize;
        }
    }

    /**
     * Find the entry with the given ID.
     *
     * @param  mixed  $id
     * @return \LaracraftTech\LaravelSpyglass\EntryResult
     */
    public function find($id): EntryResult
    {
        $entry = EntryModel::on($this->connection)->whereUuid($id)->firstOrFail();

        $tags = $this->table('spyglass_entries_tags')
                        ->where('entry_uuid', $id)
                        ->pluck('tag')
                        ->all();

        return new EntryResult(
            $entry->uuid,
            $entry->type,
            $entry->content,
            $entry->pmu,
            $entry->wt,
            $entry->cpu,
            $entry->created_at,
            $entry->prof_data,
            $tags
        );
    }

    /**
     * Return all the entries of a given type.
     *
     * @param  string|null  $type
     * @param  \LaracraftTech\LaravelSpyglass\Storage\EntryQueryOptions  $options
     * @return \Illuminate\Support\Collection|\LaracraftTech\LaravelSpyglass\EntryResult[]
     */
    public function get($type, EntryQueryOptions $options)
    {
        return EntryModel::on($this->connection)
            ->withSpyglassOptions($type, $options)
            ->take($options->limit)
            ->orderByDesc('created_at')
            ->exclude($options->exclude)
            ->get()
            ->reject(function ($entry) {
                return ! is_array($entry->content);
            })->map(function ($entry) {
                return new EntryResult(
                    $entry->uuid,
                    $entry->type,
                    $entry->content,
                    $entry->pmu,
                    $entry->wt,
                    $entry->cpu,
                    $entry->created_at,
                );
            })->values();
    }

    /**
     * Store the given array of entries.
     *
     * @param  \Illuminate\Support\Collection|\LaracraftTech\LaravelSpyglass\IncomingEntry[]  $entries
     * @return void
     */
    public function store(Collection $entries)
    {
        if ($entries->isEmpty()) {
            return;
        }

        $table = $this->table('spyglass_entries');

        $entries->chunk($this->chunkSize)->each(function ($chunked) use ($table) {
            $table->insert($chunked->map(function ($entry) {
                $entry->content = json_encode($entry->content, JSON_INVALID_UTF8_SUBSTITUTE);

                $profData = (config('spyglass.serializer') === 'json')
                    ? json_encode($entry->profData, JSON_INVALID_UTF8_SUBSTITUTE)
                    : serialize($entry->profData) ;

                $entry->profData = gzcompress($profData, config('spyglass.compress_level'));

                return $entry->toArray();
            })->toArray());
        });

        $this->storeTags($entries->pluck('tags', 'uuid'));
    }

    /**
     * Store the tags for the given entries.
     *
     * @param  \Illuminate\Support\Collection  $results
     * @return void
     */
    protected function storeTags(Collection $results)
    {
        $results->chunk($this->chunkSize)->each(function ($chunked) {
            $this->table('spyglass_entries_tags')->insert($chunked->flatMap(function ($tags, $uuid) {
                return collect($tags)->map(function ($tag) use ($uuid) {
                    return [
                        'entry_uuid' => $uuid,
                        'tag' => $tag,
                    ];
                });
            })->all());
        });
    }

    /**
     * Store the given entry updates.
     *
     * @param  \Illuminate\Support\Collection|\LaracraftTech\LaravelSpyglass\EntryUpdate[]  $updates
     * @return void
     */
    public function update(Collection $updates)
    {
        foreach ($updates as $update) {
            $entry = $this->table('spyglass_entries')
                            ->where('uuid', $update->uuid)
                            ->where('type', $update->type)
                            ->first();

            if (! $entry) {
                continue;
            }

            $content = json_encode(array_merge(
                json_decode($entry->content ?? $entry['content'] ?? [], true) ?: [], $update->changes
            ));

            $this->table('spyglass_entries')
                            ->where('uuid', $update->uuid)
                            ->where('type', $update->type)
                            ->update(['content' => $content]);

            $this->updateTags($update);
        }
    }

    /**
     * Update tags of the given entry.
     *
     * @param  \LaracraftTech\LaravelSpyglass\EntryUpdate  $entry
     * @return void
     */
    protected function updateTags($entry)
    {
        if (! empty($entry->tagsChanges['added'])) {
            $this->table('spyglass_entries_tags')->insert(
                collect($entry->tagsChanges['added'])->map(function ($tag) use ($entry) {
                    return [
                        'entry_uuid' => $entry->uuid,
                        'tag' => $tag,
                    ];
                })->toArray()
            );
        }

        collect($entry->tagsChanges['removed'])->each(function ($tag) use ($entry) {
            $this->table('spyglass_entries_tags')->where([
                'entry_uuid' => $entry->uuid,
                'tag' => $tag,
            ])->delete();
        });
    }

    /**
     * Load the monitored tags from storage.
     *
     * @return void
     */
    public function loadMonitoredTags()
    {
        try {
            $this->monitoredTags = $this->monitoring();
        } catch (\Throwable $e) {
            $this->monitoredTags = [];
        }
    }

    /**
     * Determine if any of the given tags are currently being monitored.
     *
     * @param  array  $tags
     * @return bool
     */
    public function isMonitoring(array $tags)
    {
        if (is_null($this->monitoredTags)) {
            $this->loadMonitoredTags();
        }

        return count(array_intersect($tags, $this->monitoredTags)) > 0;
    }

    /**
     * Get the list of tags currently being monitored.
     *
     * @return array
     */
    public function monitoring()
    {
        return $this->table('spyglass_monitoring')->pluck('tag')->all();
    }

    /**
     * Begin monitoring the given list of tags.
     *
     * @param  array  $tags
     * @return void
     */
    public function monitor(array $tags)
    {
        $tags = array_diff($tags, $this->monitoring());

        if (empty($tags)) {
            return;
        }

        $this->table('spyglass_monitoring')
                    ->insert(collect($tags)
                    ->mapWithKeys(function ($tag) {
                        return ['tag' => $tag];
                    })->all());
    }

    /**
     * Stop monitoring the given list of tags.
     *
     * @param  array  $tags
     * @return void
     */
    public function stopMonitoring(array $tags)
    {
        $this->table('spyglass_monitoring')->whereIn('tag', $tags)->delete();
    }

    /**
     * Prune all of the entries older than the given date.
     *
     * @param  \DateTimeInterface  $before
     * @return int
     */
    public function prune(DateTimeInterface $before)
    {
        $query = $this->table('spyglass_entries')
                ->where('created_at', '<', $before);

        $totalDeleted = 0;

        do {
            $deleted = $query->take($this->chunkSize)->delete();

            $totalDeleted += $deleted;
        } while ($deleted !== 0);

        return $totalDeleted;
    }

    /**
     * Clear all the entries.
     *
     * @return void
     */
    public function clear()
    {
        $this->table('spyglass_entries')->delete();
        $this->table('spyglass_monitoring')->delete();
    }

    /**
     * Perform any clean-up tasks needed after storing Spyglass entries.
     *
     * @return void
     */
    public function terminate()
    {
        $this->monitoredTags = null;
    }

    /**
     * Get a query builder instance for the given table.
     *
     * @param  string  $table
     * @return \Illuminate\Database\Query\Builder
     */
    protected function table($table)
    {
        return DB::connection($this->connection)->table($table);
    }
}
