<?php

namespace LaracraftTech\LaravelSpyglass\Storage;

use Illuminate\Http\Request;

class EntryQueryOptions
{
    /**
     * The tag that must belong to retrieved entries.
     *
     * @var string
     */
    public $tag;

    /**
     * The list of UUIDs of entries tor retrieve.
     *
     * @var mixed
     */
    public $uuids;

    /**
     * The number of entries to retrieve.
     *
     * @var int
     */
    public $limit = 50;

    /**
     * The entry fields to exclude from the query.
     *
     * @var int
     */
    public $exclude = [];

    /**
     * Create new entry query options from the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return static
     */
    public static function fromRequest(Request $request)
    {
        return (new static)
                ->uuids($request->uuids)
                ->tag($request->tag)
                ->limit($request->take ?? 50);
    }

    /**
     * Set the fields to exclude form the query.
     *
     * @param string $exclude
     * @return $this
     */
    public function exclude(array $exclude)
    {
        $this->exclude = array_merge($this->exclude, $exclude);

        return $this;
    }

    /**
     * Set the list of UUIDs of entries tor retrieve.
     *
     * @param  array  $uuids
     * @return $this
     */
    public function uuids(?array $uuids)
    {
        $this->uuids = $uuids;

        return $this;
    }

    /**
     * Set the tag that must belong to retrieved entries.
     *
     * @param  string  $tag
     * @return $this
     */
    public function tag(?string $tag)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Set the number of entries that should be retrieved.
     *
     * @param  int  $limit
     * @return $this
     */
    public function limit(int $limit)
    {
        $this->limit = $limit;

        return $this;
    }
}
