<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefixes
    |--------------------------------------------------------------------------
    |
    | Define custom prefixes for model types and other cache key segments.
    | Keys here override the default snake_case class basename behaviour.
    |
    | Example:
    |   'client'  => 'cl',
    |   'project' => 'proj',
    |
    */

    'prefixes' => [
        // 'client'  => 'cl',
        // 'project' => 'proj',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache TTL Values (seconds)
    |--------------------------------------------------------------------------
    |
    | Define TTL values per cache type. The 'default' key is used as a
    | fallback when no type-specific TTL is configured.
    |
    | Example:
    |   'dashboard_stats' => 300,
    |   'user_profile'    => 3600,
    |
    */

    'ttl' => [
        'default' => 300,
        'short' => 60,
        'medium' => 900,
        'long' => 3600,
        'daily' => 86400,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Tag Groups
    |--------------------------------------------------------------------------
    |
    | Map cache types to one or more tags for grouped invalidation.
    | Only used when the cache driver supports tags (Redis, Memcached).
    |
    | Example:
    |   'clients'  => ['clients'],
    |   'invoices' => ['invoices', 'billing'],
    |
    */

    'tags' => [
        // 'clients'  => ['clients'],
        // 'invoices' => ['invoices', 'billing'],
    ],

];
