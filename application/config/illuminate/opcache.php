<?php

return [
    'url' => base_url(),
    'prefix' => 'opcache-api',
    'verify' => true,
    'headers' => [],
    'directories' => [
        base_path('application'),
        base_path('system'),
        base_path('vendor'),
    ],
    'exclude' => [
        'test',
        'Test',
        'tests',
        'Tests',
        'stub',
        'Stub',
        'stubs',
        'Stubs',
        'dumper',
        'Dumper',
        'Autoload',
    ],
];
