<?php

return [
    'default' => env('DB_CONNECTION', 'pgsql'),

    'connections' => [
        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'db'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'simple_stock_flow'),
            'username' => env('DB_USERNAME', 'simple_stock_flow'),
            'password' => env('DB_PASSWORD', 'postgres_secret_123'),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'sales,public',
            'sslmode' => 'prefer',
        ],
    ],

    'migrations' => 'migrations',
];
