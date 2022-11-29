<?php

namespace LaracraftTech\LaravelSpyglass;

class Spyglass
{
    use AuthorizesRequest;

    /**
     * Indicates if Spyglass migrations will be run.
     *
     * @var bool
     */
    public static $runsMigrations = true;

    /**
     * Indicates if Spyglass should use the dark theme.
     *
     * @var bool
     */
    public static $useDarkTheme = false;

    /**
     * Get the default JavaScript variables for Spyglass.
     *
     * @return array
     */
    public static function scriptVariables()
    {
        return [
            'path' => config('spyglass.path'),
            'timezone' => config('app.timezone'),
            'recording' => ! cache('spyglass:pause-recording'),
        ];
    }
}
