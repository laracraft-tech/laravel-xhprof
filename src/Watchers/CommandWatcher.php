<?php

namespace LaracraftTech\LaravelSpyglass\Watchers;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use LaracraftTech\LaravelSpyglass\IncomingEntry;
use LaracraftTech\LaravelSpyglass\Spyglass;

class CommandWatcher extends Watcher
{
    /**
     * Register the watcher.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function register($app)
    {
        $app['events']->listen(CommandStarting::class, [$this, 'startCommand']);
        $app['events']->listen(CommandFinished::class, [$this, 'finishCommand']);
    }

    public function startCommand(CommandStarting $event)
    {
        $this->xhprof->enable();
    }

    /**
     * Record an Artisan command was executed.
     *
     * @param  \Illuminate\Console\Events\CommandFinished  $event
     * @return void
     */
    public function finishCommand(CommandFinished $event)
    {
        if (! Spyglass::isRecording() || $this->shouldIgnore($event)) {
            return;
        }

        $this->xhprof->disable();

        Spyglass::recordCommand(IncomingEntry::make([
            'command' => $event->command ?? $event->input->getArguments()['command'] ?? 'default',
            'exit_code' => $event->exitCode,
            'arguments' => $event->input->getArguments(),
            'options' => $event->input->getOptions(),
        ], $this->xhprof->getData()));
    }

    /**
     * Determine if the event should be ignored.
     *
     * @param  mixed  $event
     * @return bool
     */
    private function shouldIgnore($event)
    {
        return in_array($event->command, array_merge($this->options['ignore'] ?? [], [
            'schedule:run',
            'schedule:finish',
            'package:discover',
        ]));
    }
}
