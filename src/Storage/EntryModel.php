<?php

namespace LaracraftTech\LaravelSpyglass\Storage;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use LaracraftTech\LaravelSpyglass\Database\Factories\EntryModelFactory;
use LaracraftTech\LaravelUsefulTraits\Scopes\ScopeSelectAllBut;

class EntryModel extends Model
{
    use HasFactory, ScopeSelectAllBut;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spyglass_entries';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = null;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'content' => 'json',
    ];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'uuid';

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Prevent Eloquent from overriding uuid with `lastInsertId`.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Scope the query for the given query options.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @param  \LaracraftTech\LaravelSpyglass\Storage\EntryQueryOptions  $options
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithSpyglassOptions($query, $type, EntryQueryOptions $options)
    {
        $this->whereType($query, $type)
            ->whereTag($query, $options)
            ->filter($query, $options);

        return $query;
    }

    /**
     * Scope the query for the given type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return $this
     */
    protected function whereType($query, $type)
    {
        $query->when($type, function ($query, $type) {
            return $query->where('type', $type);
        });

        return $this;
    }

    /**
     * Scope the query for the given type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \LaracraftTech\LaravelSpyglass\Storage\EntryQueryOptions  $options
     * @return $this
     */
    protected function whereTag($query, EntryQueryOptions $options)
    {
        $query->when($options->tag, function ($query, $tag) {
            return $query->whereIn('uuid', function ($query) use ($tag) {
                $query->select('entry_uuid')->from('spyglass_entries_tags')->whereTag($tag);
            });
        });

        return $this;
    }

    /**
     * Scope the query for the given display options.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \LaracraftTech\LaravelSpyglass\Storage\EntryQueryOptions  $options
     * @return $this
     */
    protected function filter($query, EntryQueryOptions $options)
    {
        if ($options->tag) {
            return $this;
        }

        return $this;
    }

    /**
     * Get the current connection name for the model.
     *
     * @return string
     */
    public function getConnectionName()
    {
        return config('spyglass.storage.database.connection');
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public static function newFactory()
    {
        return EntryModelFactory::new();
    }

    /**
     * Get the profiling data.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function profData(): Attribute
    {
        return Attribute::make(
            get: function($value) {
                $profData = gzuncompress($value);

                $profData = (config('spyglass.serializer') === 'json')
                    ? json_decode($profData, true)
                    : unserialize($profData) ;

                return collect($profData);
            },
        );
    }

    /**
     * The list of possible metrics collected as part of XHProf that
     * require inclusive/exclusive handling while reporting.
     *
     * @return array
     */
    public function getPossibletMetrics(): array
    {
        return [
            "ct" =>     ["Call", "amount", "call time"],
            "wt" =>     ["Wall", "microsecs", "walltime"],
            "cpu" =>    ["Cpu", "microsecs", "cpu time"],
            "mu" =>     ["MUse", "bytes", "memory usage"],
            "pmu" =>    ["PMUse", "bytes", "peak memory usage"],
        ];
    }

    public function getMetrics()
    {
        return array_keys($this->getPossibletMetrics());
    }

    /**
     * Takes a parent/child function name encoded as
     * "a==>b" and returns array("a", "b").
     */
    function parseParentChild($parent_child): array
    {
        $ret = explode("==>", $parent_child);

        // Return if both parent and child are set
        if (isset($ret[1])) {
            return $ret;
        }

        return array(null, $ret[0]);
    }

    /**
     * @return array|void
     */
    public function getComputedInclusiveProfData()
    {
        $metrics = $this->getMetrics();

        $computed = [];

        foreach ($this->prof_data as $parent_child => $info)
        {
            list($parent, $child) = $this->parseParentChild($parent_child);

            if ($parent == $child) {
                /*
                 * XHProf PHP extension should never trigger this situation any more.
                 * Recursion is handled in the XHProf PHP extension by giving nested
                 * calls a unique recursion-depth appended name (for example, foo@1).
                 */
                xhprof_error("Error in Raw Data: parent & child are both: $parent");
                return;
            }

            if (!isset($computed[$child])) {
                foreach ($metrics as $metric) {
                    $computed[$child][$metric] = $info[$metric];
                }
            } else {
                /* update inclusive times/metric for this child  */
                foreach ($metrics as $metric) {
                    $computed[$child][$metric] += $info[$metric];
                }
            }
        }

        return $computed;
    }

    /**
     * @return array|void
     */
    public function getComputedProfData()
    {
        $metrics = $this->getMetrics();

        $computed = $this->getComputedInclusiveProfData();

        /*
         * initialize exclusive (self) metric value to inclusive metric value to start with.
         * In the same pass, also add up the total number of function calls.
         */
        foreach ($computed as $symbol => $info) {
            foreach ($metrics as $metric) {
                $computed[$symbol]["excl_" . $metric] = $computed[$symbol][$metric];
            }

            $computed[$symbol]['symbol'] = $symbol;
        }

        /* adjust exclusive times by deducting inclusive time of children */
        foreach ($this->prof_data as $parent_child => $info) {
            list($parent, $child) = $this->parseParentChild($parent_child);

            if ($parent) {
                foreach ($metrics as $metric) {
                    // make sure the parent exists hasn't been pruned.
                    if (isset($computed[$parent])) {
                        $computed[$parent]["excl_" . $metric] -= $info[$metric];
                    }
                }
            }
        }

        return $computed;
    }
}
