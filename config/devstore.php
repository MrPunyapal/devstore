<?php

return [
    'nl_model_id' => env('TYPESENSE_NL_MODEL_ID', ''),
    'query_by' => env('TYPESENSE_QUERY_BY', 'name,brand,category,description,processor'),
    'search_debug' => env('DEVSTORE_SEARCH_DEBUG', false),
];
