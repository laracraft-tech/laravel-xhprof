<?php

namespace LaracraftTech\LaravelSpyglass\Console;

use Illuminate\Console\Command;
use Laravel\Spyglass\Contracts\ClearableRepository;

class ClearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spyglass:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all entries from Spyglass';

    /**
     * Execute the console command.
     *
     * @param  \Laravel\Spyglass\Contracts\ClearableRepository  $storage
     * @return void
     */
    public function handle(ClearableRepository $storage)
    {
        $storage->clear();

        $this->info('Spyglass entries cleared!');
    }
}
