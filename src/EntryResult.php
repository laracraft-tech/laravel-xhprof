<?php

namespace LaracraftTech\LaravelSpyglass;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use JsonSerializable;

class EntryResult implements JsonSerializable
{
    /**
     * The entry's primary key.
     *
     * @var mixed
     */
    public $id;

    /**
     * The entry's type.
     *
     * @var string
     */
    public $type;

    /**
     * The entry's content.
     *
     * @var array
     */
    public $content = [];

    /**
     * The entry's profiling data.
     *
     * @var array
     */
    public $profData = [];

    /**
     * The Peak Memory Usage sum.
     *
     * @var array
     */
    public $pmu = 0;

    /**
     * The Wall Time sum.
     *
     * @var array
     */
    public $wt = 0;

    /**
     * The CPU sum.
     *
     * @var array
     */
    public $cpu = 0;

    /**
     * The datetime that the entry was recorded.
     *
     * @var \Carbon\CarbonInterface|\Carbon\Carbon
     */
    public $createdAt;

    /**
     * The tags assigned to the entry.
     *
     * @var array
     */
    private $tags;

    /**
     * The generated URL to the entry user's avatar.
     *
     * @var string
     */
    protected $avatar;

    /**
     * Create a new entry result instance.
     *
     * @param mixed $id
     * @param string $type
     * @param array $content
     * @param int $pmu
     * @param int $wt
     * @param int $cpu
     * @param CarbonInterface $createdAt
     * @param array $profData
     * @param array $tags
     */
    public function __construct(
        $id,
        string $type,
        array $content,
        int $pmu,
        int $wt,
        int $cpu,
        CarbonInterface $createdAt,
        array $profData = [],
        array $tags = []
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->tags = $tags;
        $this->content = $content;
        $this->profData = $profData;
        $this->pmu = $pmu;
        $this->wt = $wt;
        $this->cpu = $cpu;
        $this->createdAt = $createdAt;
    }

    /**
     * Set the URL to the entry user's avatar.
     *
     * @return $this
     */
    public function generateAvatar()
    {
        $this->avatar = Avatar::url($this->content['user'] ?? []);

        return $this;
    }

    /**
     * Get the array representation of the entry.
     *
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $data = [
            'id' => $this->id,
            'type' => $this->type,
            'content' => $this->content,
            'pmu' => number_format($this->pmu),
            'wt' => floor($this->wt / 1000),
            'cpu' => number_format($this->cpu),
            'tags' => $this->tags,
            'created_at' => $this->createdAt->toDateTimeString(),
        ];

        if (!empty($this->profData)) {
            $data['profData'] = $this->profData;
        }

        return collect($data)->when($this->avatar, function ($items) {
            return $items->mergeRecursive([
                'content' => [
                    'user' => [
                        'avatar' => $this->avatar,
                    ],
                ],
            ]);
        })->all();
    }
}
