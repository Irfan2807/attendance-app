<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Performance Optimization Settings
    |--------------------------------------------------------------------------
    */

    'enable_query_caching' => env('CACHE_QUERIES', true),

    'cache_duration' => env('CACHE_DURATION', 3600), // 1 hour default

    // Enable for production only
    'enable_response_caching' => env('APP_ENV', 'production') === 'production',

];
