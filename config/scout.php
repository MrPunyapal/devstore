<?php

use App\Models\Product;

return [
    'driver' => env('SCOUT_DRIVER', 'typesense'), 'prefix' => '', 'queue' => false, 'after_commit' => false,
    'chunk' => ['searchable' => 100, 'unsearchable' => 100], 'soft_delete' => false, 'identify' => false,
    'typesense' => [
        'client-settings' => [
            'api_key' => env('TYPESENSE_API_KEY', ''),
            'nodes' => [['host' => env('TYPESENSE_HOST', '127.0.0.1'), 'port' => env('TYPESENSE_PORT', '8108'), 'protocol' => env('TYPESENSE_PROTOCOL', 'http'), 'path' => env('TYPESENSE_PATH', '')]],
            'connection_timeout_seconds' => (int) env('TYPESENSE_CONNECTION_TIMEOUT_SECONDS', 60),
            'num_retries' => 0, 'healthcheck_interval_seconds' => 30, 'retry_interval_seconds' => 1,
        ],
        'model-settings' => [Product::class => [
            'collection-schema' => [
                'fields' => [
                    ['name' => 'id', 'type' => 'string'], ['name' => 'name', 'type' => 'string'],
                    ['name' => 'brand', 'type' => 'string', 'facet' => true], ['name' => 'category', 'type' => 'string', 'facet' => true],
                    ['name' => 'description', 'type' => 'string'], ['name' => 'price', 'type' => 'float', 'sort' => true],
                    ['name' => 'ram_gb', 'type' => 'int32', 'optional' => true, 'facet' => true], ['name' => 'storage_gb', 'type' => 'int32', 'optional' => true, 'facet' => true],
                    ['name' => 'processor', 'type' => 'string', 'optional' => true], ['name' => 'screen_size', 'type' => 'float', 'optional' => true],
                    ['name' => 'weight_kg', 'type' => 'float'], ['name' => 'rating', 'type' => 'float', 'sort' => true],
                    ['name' => 'in_stock', 'type' => 'bool', 'facet' => true], ['name' => 'wireless', 'type' => 'bool', 'optional' => true, 'facet' => true],
                    ['name' => 'created_at', 'type' => 'int64', 'sort' => true],
                ], 'default_sorting_field' => 'rating',
            ], 'search-parameters' => ['query_by' => env('TYPESENSE_QUERY_BY', 'name,brand,category,description,processor')],
        ]], 'import_action' => 'upsert',
    ],
];
