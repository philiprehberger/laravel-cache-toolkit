# Laravel Cache Toolkit

Standardized cache key builder and tag-aware cache operations for Laravel with graceful fallback for non-tagging drivers.

## Requirements

- PHP 8.2+
- Laravel 11 or 12

## Installation

```bash
composer require philiprehberger/laravel-cache-toolkit
```

The service provider is auto-discovered by Laravel. Optionally publish the config file:

```bash
php artisan vendor:publish --tag=cache-toolkit-config
```

This places `config/cache-toolkit.php` into your application.

---

## CacheKeyBuilder

`CacheKeyBuilder` is a static utility class for building consistent, predictable cache keys.

### make — arbitrary parts

```php
use PhilipRehberger\CacheToolkit\CacheKeyBuilder;

CacheKeyBuilder::make('client', '123', 'stats');
// "client:123:stats"

// Empty parts are filtered out automatically
CacheKeyBuilder::make('client', '', '123');
// "client:123"
```

### forModel — Eloquent model instance

```php
CacheKeyBuilder::forModel($client);
// "client:42"

CacheKeyBuilder::forModel($client, 'details');
// "client:42:details"
```

The prefix is derived from the snake_case class basename (e.g. `ProjectFile` → `project_file`), or overridden via the `prefixes` config.

### forModelType — class name + ID

```php
CacheKeyBuilder::forModelType(Client::class, 42);
// "client:42"

CacheKeyBuilder::forModelType(Client::class, 42, 'invoices');
// "client:42:invoices"
```

### forList — collections with optional filters

```php
CacheKeyBuilder::forList('clients');
// "clients:list:all"

CacheKeyBuilder::forList('clients', ['active' => 1, 'stage' => 'prospect']);
// "clients:list:<md5-hash>"
```

### forPaginated

```php
CacheKeyBuilder::forPaginated('clients', page: 2, perPage: 15);
// "clients:page:2:15:all"

CacheKeyBuilder::forPaginated('clients', 1, 10, ['status' => 'active']);
// "clients:page:1:10:<md5-hash>"
```

### forAnalytics

```php
CacheKeyBuilder::forAnalytics('revenue', '2026-01-01', '2026-03-31');
// "analytics:revenue:2026-01-01:2026-03-31"

CacheKeyBuilder::forAnalytics('revenue');
// "analytics:revenue:all:all"
```

### forUser

```php
CacheKeyBuilder::forUser(userId: 5, type: 'dashboard');
// "user:5:dashboard"
```

### forDateRange

```php
CacheKeyBuilder::forDateRange('reports', '2026-01-01', '2026-01-31');
// "reports:2026-01-01:2026-01-31"
```

### TTL helpers

```php
// Returns integer seconds from config('cache-toolkit.ttl.<type>')
CacheKeyBuilder::ttl('dashboard_stats');
// 300

// Returns a DateTimeInterface (Carbon) for use with Cache::put()
CacheKeyBuilder::ttlCarbon('dashboard_stats');
```

### Tag helpers

```php
// Returns array of tags from config('cache-toolkit.tags.<type>')
CacheKeyBuilder::tags('clients');
// ['clients', 'crm']
```

### Driver detection

```php
CacheKeyBuilder::supportsTags();
// true  — when driver is redis or memcached
// false — file, array, database, etc.
```

---

## CacheTagManager

`CacheTagManager` wraps Laravel's Cache facade with tag-aware operations. When the configured driver does not support tags (file, array, database…) all operations fall back transparently to plain cache calls.

Resolve it via the service container or use the `CacheTag` facade:

```php
use PhilipRehberger\CacheToolkit\CacheTagManager;
use PhilipRehberger\CacheToolkit\Facades\CacheTag;

$manager = app(CacheTagManager::class);
```

### remember

```php
$value = $manager->remember(
    ttlKey:   'dashboard_stats',   // resolved via config('cache-toolkit.ttl.dashboard_stats')
    callback: fn () => expensiveQuery(),
    tags:     ['clients'],
    // remaining args become the cache key parts
    'client', '42', 'stats'
);
```

### put / get / has / forget

```php
$key = CacheKeyBuilder::forModel($client, 'profile');

$manager->put($key, $data, ttl: 300, tags: ['clients']);
$manager->get($key, tags: ['clients']);
$manager->has($key, tags: ['clients']);
$manager->forget($key, tags: ['clients']);
```

All four methods accept an optional `$tags` array. Tags are ignored when the driver does not support them.

### flush — invalidate by tags

```php
// Flush everything tagged 'clients'
$manager->flush(['clients']);

// Flush using the tags configured for a type
$manager->flushType('clients');    // reads config('cache-toolkit.tags.clients')

// Flush multiple types, returns ['clients' => bool, 'invoices' => bool]
$manager->flushTypes(['clients', 'invoices']);
```

`flush` and `flushType` return `false` (and log a debug message) when the driver does not support tags, so callers never need to guard against exceptions.

---

## Tag fallback behaviour

| Driver     | Tags supported | Behaviour                                               |
|------------|----------------|---------------------------------------------------------|
| `redis`    | Yes            | Tags used for grouping and invalidation                 |
| `memcached`| Yes            | Tags used for grouping and invalidation                 |
| `file`     | No             | Operations fall back to plain cache; `flush` returns `false` |
| `array`    | No             | Operations fall back to plain cache; `flush` returns `false` |
| `database` | No             | Operations fall back to plain cache; `flush` returns `false` |

No exceptions are thrown on unsupported drivers — the toolkit degrades gracefully.

---

## Facades

```php
use PhilipRehberger\CacheToolkit\Facades\CacheKey;
use PhilipRehberger\CacheToolkit\Facades\CacheTag;

CacheKey::forModel($client, 'stats');
CacheTag::remember('ttl_key', fn () => ..., ['clients'], 'client', '42', 'stats');
```

---

## Configuration reference

`config/cache-toolkit.php`:

```php
return [

    // Override the snake_case class-basename prefix for model types
    'prefixes' => [
        // 'client'  => 'cl',
        // 'project' => 'proj',
    ],

    // TTL values in seconds; 'default' is the fallback
    'ttl' => [
        'default' => 300,
        'short'   => 60,
        'medium'  => 900,
        'long'    => 3600,
        'daily'   => 86400,
    ],

    // Map cache types to tag arrays for grouped invalidation
    'tags' => [
        // 'clients'  => ['clients'],
        // 'invoices' => ['invoices', 'billing'],
    ],

];
```

---

## Running the tests

```bash
composer install
vendor/bin/phpunit
```

---

## License

MIT License. Copyright (c) 2026 Philip Rehberger. See [LICENSE](LICENSE) for details.
