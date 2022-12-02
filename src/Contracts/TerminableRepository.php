<?php

namespace LaracraftTech\LaravelSpyglass\Contracts;

interface TerminableRepository
{
    /**
     * Perform any clean-up tasks needed after storing Spyglass entries.
     *
     * @return void
     */
    public function terminate();
}
