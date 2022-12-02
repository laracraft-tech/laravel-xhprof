<?php

namespace LaracraftTech\LaravelSpyglass\Console;

use Illuminate\Console\Command;
use LaracraftTech\LaravelSpyglass\Contracts\ClearableRepository;

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
     * @param  \LaracraftTech\LaravelSpyglass\Contracts\ClearableRepository  $storage
     * @return void
     */
    public function handle(ClearableRepository $storage)
    {
        $storage->clear();

        $this->info('Spyglass entries cleared!');
    }
}
