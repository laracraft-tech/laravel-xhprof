<?php

namespace LaracraftTech\LaravelSpyglass;

trait RegistersWatchers
{
    /**
     * The class names of the registered watchers.
     *
     * @var array
     */
    protected static $watchers = [];

    /**
     * Determine if a given watcher has been registered.
     *
     * @param  string  $class
     * @return bool
     */
    public static function hasWatcher($class)
    {
        return in_array($class, static::$watchers);
    }

    /**
     * Register the configured Spyglass watchers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected static function registerWatchers($app)
    {
        foreach (config('spyglass.watchers') as $key => $watcher) {
            if (is_string($key) && $watcher === false) {
                continue;
            }

            if (is_array($watcher) && ! ($watcher['enabled'] ?? true)) {
                continue;
            }

            $watcher = $app->make(is_string($key) ? $key : $watcher, [
                'xhprof' => $app->make(XHProf::class),
                'options' => is_array($watcher) ? $watcher : [],
            ]);

            static::$watchers[] = get_class($watcher);

            $watcher->register($app);
        }
    }
}
