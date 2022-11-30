<?php

namespace LaracraftTech\LaravelSpyglass\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class PauseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spyglass:pause';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pause all Spyglass watchers';

    /**
     * The cache repository implementation.
     *
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $cache;

    /**
     * Create a new command instance.
     *
     * @param  \Illuminate\Contracts\Cache\Repository  $cache
     * @return void
     */
    public function __construct(CacheRepository $cache)
    {
        parent::__construct();

        $this->cache = $cache;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (! $this->cache->get('spyglass:pause-recording')) {
            $this->cache->put('spyglass:pause-recording', true, now()->addDays(30));
        }

        $this->info('Spyglass watchers paused successfully.');
    }
}
