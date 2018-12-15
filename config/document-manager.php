<?php

return [
    // Name of the MongoDB connection from database.php
    'connection' => 'mongodb',

    'paths' => [
        base_path('app/Documents'),
    ],

    'proxies' => [
        'namespace' => 'Proxies',
        'path' => storage_path('proxies'),
        'auto_generate' => false,
    ],

    'hydrators' => [
        'namespace' => 'Hydrators',
        'path' => storage_path('hydrators'),
        'auto_generate' => false,
    ],

    // Only annotations metadata available
    'meta' => 'annotations',
];