<?php

namespace LaracraftTech\LaravelSpyglass;

use Illuminate\Support\Str;
use LaracraftTech\LaravelSpyglass\Contracts\EntriesRepository;

class IncomingEntry
{
    /**
     * The entry's UUID.
     *
     * @var string
     */
    public $uuid;

    /**
     * The entry's type.
     *
     * @var string
     */
    public $type;

    /**
     * The currently authenticated user, if applicable.
     *
     * @var mixed
     */
    public $user;

    /**
     * The entry's content.
     *
     * @var array
     */
    public $content = [];

    /**
     * The entry's tags.
     *
     * @var array
     */
    public $tags = [];

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
     * The DateTime that indicates when the entry was recorded.
     *
     * @var \DateTimeInterface
     */
    public $recordedAt;

    /**
     * Create a new incoming entry instance.
     *
     * @param array $content
     * @param array $profData
     * @param null $uuid
     */
    public function __construct(array $content, array $profData, $uuid = null)
    {
        $this->uuid = $uuid ?: (string) Str::orderedUuid();

        $this->recordedAt = now();

        $this->profData = $profData;

        $this->pmu = $this->profData['main()']['pmu'] ?? 0;
        $this->wt = $this->profData['main()']['wt'] ?? 0;
        $this->cpu = $this->profData['main()']['cpu'] ?? 0;

        $this->content = array_merge(
            $content,
            ['hostname' => gethostname()]
        );

        if (!isset($this->content['slow'])) {
            $this->content['slow'] = isset($this->options['slow']) && $this->wt >= $this->options['slow'];
        }
    }

    /**
     * Create a new entry instance.
     *
     * @param  mixed  ...$arguments
     * @return static
     */
    public static function make(...$arguments)
    {
        return new static(...$arguments);
    }

    /**
     * Assign the entry a given type.
     *
     * @param  string  $type
     * @return $this
     */
    public function type(string $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Set the currently authenticated user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return $this
     */
    public function user($user)
    {
        $this->user = $user;

        $this->content = array_merge($this->content, [
            'user' => [
                'id' => $user->getAuthIdentifier(),
                'name' => $user->name ?? null,
                'email' => $user->email ?? null,
            ],
        ]);

        $this->tags(['Auth:'.$user->getAuthIdentifier()]);

        return $this;
    }

    /**
     * Merge tags into the entry's existing tags.
     *
     * @param  array  $tags
     * @return $this
     */
    public function tags(array $tags)
    {
        $this->tags = array_unique(array_merge($this->tags, $tags));

        return $this;
    }

    /**
     * Determine if the incoming entry has a monitored tag.
     *
     * @return bool
     */
    public function hasMonitoredTag()
    {
        if (! empty($this->tags)) {
            return app(EntriesRepository::class)->isMonitoring($this->tags);
        }

        return false;
    }

    /**
     * Determine if the incoming entry is a request.
     *
     * @return bool
     */
    public function isRequest()
    {
        return $this->type === EntryType::REQUEST;
    }

    /**
     * Determine if the incoming entry is a failed request.
     *
     * @return bool
     */
    public function isFailedRequest()
    {
        return $this->type === EntryType::REQUEST &&
            ($this->content['response_status'] ?? 200) >= 500;
    }

    /**
     * Determine if the incoming entry is a failed job.
     *
     * @return bool
     */
    public function isFailedJob()
    {
        return $this->type === EntryType::JOB &&
               ($this->content['status'] ?? null) === 'failed';
    }

    /**
     * Determine if the incoming entry is a scheduled task.
     *
     * @return bool
     */
    public function isScheduledTask()
    {
        return $this->type === EntryType::SCHEDULED_TASK;
    }

    /**
     * Determine if the incoming entry is a slow.
     *
     * @return bool
     */
    public function isSlow()
    {
        return ($this->content['slow'] ?? false);
    }

    /**
     * Get an array representation of the entry for storage.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'uuid' => $this->uuid,
            'type' => $this->type,
            'content' => $this->content,
            'prof_data' => $this->profData,
            'pmu' => $this->pmu,
            'wt' => $this->wt,
            'cpu' => $this->cpu,
            'created_at' => $this->recordedAt->toDateTimeString(),
        ];
    }
}
