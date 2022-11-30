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
     * The list of hidden request headers.
     *
     * @var array
     */
    public static $hiddenRequestHeaders = [
        'authorization',
        'php-auth-pw',
    ];

    /**
     * The list of hidden request parameters.
     *
     * @var array
     */
    public static $hiddenRequestParameters = [
        'password',
        'password_confirmation',
    ];

    /**
     * The list of hidden response parameters.
     *
     * @var array
     */
    public static $hiddenResponseParameters = [];

    /**
     * Indicates if Spyglass should use the dark theme.
     *
     * @var bool
     */
    public static $useDarkTheme = false;

    /**
     * Specifies that Spyglass should use the dark theme.
     *
     * @return static
     */
    public static function night()
    {
        static::$useDarkTheme = true;

        return new static;
    }

    /**
     * Hide the given request header.
     *
     * @param  array  $headers
     * @return static
     */
    public static function hideRequestHeaders(array $headers)
    {
        static::$hiddenRequestHeaders = array_merge(
            static::$hiddenRequestHeaders,
            $headers
        );

        return new static;
    }

    /**
     * Hide the given request parameters.
     *
     * @param  array  $attributes
     * @return static
     */
    public static function hideRequestParameters(array $attributes)
    {
        static::$hiddenRequestParameters = array_merge(
            static::$hiddenRequestParameters,
            $attributes
        );

        return new static;
    }

    /**
     * Hide the given response parameters.
     *
     * @param  array  $attributes
     * @return static
     */
    public static function hideResponseParameters(array $attributes)
    {
        static::$hiddenResponseParameters = array_merge(
            static::$hiddenResponseParameters,
            $attributes
        );

        return new static;
    }

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
