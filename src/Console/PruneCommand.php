<?php

namespace LaracraftTech\LaravelSpyglass\Console;

use Illuminate\Console\Command;
use Laravel\Spyglass\Contracts\PrunableRepository;

class PruneCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spyglass:prune {--hours=24 : The number of hours to retain Spyglass data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune stale entries from the Spyglass database';

    /**
     * Execute the console command.
     *
     * @param  \Laravel\Spyglass\Contracts\PrunableRepository  $repository
     * @return void
     */
    public function handle(PrunableRepository $repository)
    {
        $this->info($repository->prune(now()->subHours($this->option('hours'))).' entries pruned.');
    }
}
