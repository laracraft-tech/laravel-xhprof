<?php

return [
    'enabled' => env('XHPROF_ENABLED', false),

    // Allow to skip profiling for some URIs
    'skip' => [
		'/__clockwork/',
		'/_debugbar/',
	],
];
