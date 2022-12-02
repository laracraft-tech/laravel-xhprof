<?php

namespace LaracraftTech\LaravelSpyglass\Watchers;

use LaracraftTech\LaravelSpyglass\XHProf;

abstract class Watcher
{
    /**
     * The given XHProf instance.
     *
     * @var XHProf
     */
    protected $xhprof;

    /**
     * The configured watcher options.
     *
     * @var array
     */
    public $options = [];

    /**
     * Create a new watcher instance.
     *
     * @param XHProf $xhprof
     * @param array $options
     */
    public function __construct(XHProf $xhprof, array $options = [])
    {
        $this->xhprof = $xhprof;
        $this->options = $options;
    }

    /**
     * Register the watcher.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    abstract public function register($app);
}
