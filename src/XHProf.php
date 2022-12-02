<?php

namespace LaracraftTech\LaravelSpyglass;

use Exception;
use Illuminate\Support\Str;

class XHProf
{
    /**
     * The data of the profiling.
     *
     * @var array
     */
    private $data = [];

    /**
     * The profiler extensions which are supported.
     *
     * @var string[]
     */
    private $supportedExtensions = [
        'xhprof',
        'tideways',
        'tideways_xhprof',
    ];

    /**
     * The extension name.
     *
     * @var string
     */
    private $extensionName = '';

    /**
     * Flgas for the profiling.
     *
     * @var \Illuminate\Support\Collection
     */
    private $extensionFlags = [];

    /**
     * Functions to ignore in profiling.
     *
     * @var array
     */
    private $ignoredFunctions = [];

    /**
     * Create a new XHProf instance.
     *
     * @link https://www.php.net/manual/de/book.xhprof.php
     *
     * @param string $extensionName
     * @param array $extensionFlags
     * @param array $ignoredFunctions
     * @throws Exception
     */
    public function __construct(string $extensionName, array $extensionFlags, array $ignoredFunctions)
    {
        if (!in_array($extensionName, $this->supportedExtensions)) {
            $separator = ', ';
            $supportedString = Str::replaceLast($separator, '', implode($separator, $this->supportedExtensions));
            $message = "The $extensionName profiler extension is not supported! Supported are: ". $supportedString;
            throw new Exception($message);
        }

        if (!extension_loaded($extensionName)) {
            throw new Exception("The $extensionName extension is not installed or enable!");
        }

        $this->extensionName = $extensionName;

        $this->extensionFlags = collect($extensionFlags)->map(function ($flag) {
            return constant(strtoupper($this->extensionName.'_flags_'.$flag));
        });

        $this->ignoredFunctions = $ignoredFunctions;
    }

    /**
     * Enable the profiler.
     *
     * @return void
     */
    public function enable()
    {
        call_user_func($this->extensionName.'_'.'enable',
            $this->extensionFlags->sum(),
            ['ignored_functions' => $this->ignoredFunctions],
        );
    }

    /**
     * Disable the profiler.
     *
     * @return void
     */
    public function disable()
    {
        $this->data = call_user_func($this->extensionName.'_'.'disable');
    }

    /**
     * Get the profiled data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}
