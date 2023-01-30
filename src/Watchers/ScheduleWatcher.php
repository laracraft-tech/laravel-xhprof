<?php

namespace LaracraftTech\LaravelSpyglass\Watchers;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use LaracraftTech\LaravelSpyglass\IncomingEntry;
use LaracraftTech\LaravelSpyglass\Spyglass;

class ScheduleWatcher extends Watcher
{
    /**
     * Register the watcher.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function register($app)
    {
        $app['events']->listen(CommandStarting::class, [$this, 'recordCommand']);
    }

    /**
     * Record a scheduled command was executed.
     *
     * @param  \Illuminate\Console\Events\CommandStarting  $event
     * @return void
     */
    public function recordCommand(CommandStarting $event)
    {
        if (! Spyglass::isRecording() ||
            $event->command !== 'schedule:run' &&
            $event->command !== 'schedule:finish') {
            return;
        }

        collect(app(Schedule::class)->events())->each(function ($event) {
            $event->then(function () use ($event) {
                Spyglass::recordScheduledCommand(IncomingEntry::make([
                    'command' => $event instanceof CallbackEvent ? 'Closure' : $event->command,
                    'description' => $event->description,
                    'expression' => $event->expression,
                    'timezone' => $event->timezone,
                    'user' => $event->user,
                    'output' => $this->getEventOutput($event),
                ], []));
            });
        });
    }

    /**
     * Get the output for the scheduled event.
     *
     * @param  \Illuminate\Console\Scheduling\Event  $event
     * @return string|null
     */
    protected function getEventOutput(Event $event)
    {
        if (! $event->output ||
            $event->output === $event->getDefaultOutput() ||
            $event->shouldAppendOutput ||
            ! file_exists($event->output)) {
            return '';
        }

        return trim(file_get_contents($event->output));
    }
}
