<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Persist
    |--------------------------------------------------------------------------
    |
    | When false (default), uses in-memory storage that is automatically
    | cleaned up when the request ends. When true, uses encrypted cache
    | storage that persists across requests until TTL expires.
    |
    */

    'persist' => env('CLOAK_PERSIST', false),

    /*
    |--------------------------------------------------------------------------
    | Storage Driver
    |--------------------------------------------------------------------------
    |
    | The storage driver class that implements StoreInterface. This class
    | handles persisting the placeholder-to-value mappings when persist
    | is true. The default CacheStorage uses Laravel's cache with encryption.
    |
    */

    'storage_driver' => DynamikDev\Cloak\Laravel\CacheStorage::class,

    /*
    |--------------------------------------------------------------------------
    | Cache Store
    |--------------------------------------------------------------------------
    |
    | When using CacheStorage, this specifies which Laravel cache store
    | to use. Set to null to use the default cache store, or specify
    | a store name like 'redis', 'memcached', 'file', etc.
    |
    */

    'cache_store' => env('CLOAK_CACHE_STORE'),

    /*
    |--------------------------------------------------------------------------
    | Default TTL
    |--------------------------------------------------------------------------
    |
    | The default time-to-live in seconds for cached mappings. After this
    | time, the cached data will expire and uncloaking will not be possible.
    |
    */

    'default_ttl' => env('CLOAK_DEFAULT_TTL', 3600),

];
