# Laravel Cache Toolkit

[![Tests](https://github.com/philiprehberger/laravel-cache-toolkit/actions/workflows/tests.yml/badge.svg)](https://github.com/philiprehberger/laravel-cache-toolkit/actions/workflows/tests.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/philiprehberger/laravel-cache-toolkit.svg)](https://packagist.org/packages/philiprehberger/laravel-cache-toolkit)
[![Last updated](https://img.shields.io/github/last-commit/philiprehberger/laravel-cache-toolkit)](https://github.com/philiprehberger/laravel-cache-toolkit/commits/main)

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

## Usage

### CacheKeyBuilder

`CacheKeyBuilder` is a static utility class for building consistent, predictable cache keys.

```php
use PhilipRehberger\CacheToolkit\CacheKeyBuilder;

CacheKeyBuilder::make('client', '123', 'stats');
// "client:123:stats"

CacheKeyBuilder::forModel($client, 'details');
// "client:42:details"

CacheKeyBuilder::forList('clients', ['active' => 1]);
// "clients:list:<md5-hash>"

CacheKeyBuilder::forPaginated('clients', page: 2, perPage: 15);
// "clients:page:2:15:all"

CacheKeyBuilder::forAnalytics('revenue', '2026-01-01', '2026-03-31');
// "analytics:revenue:2026-01-01:2026-03-31"

CacheKeyBuilder::forUser(userId: 5, type: 'dashboard');
// "user:5:dashboard"
```

### CacheTagManager

`CacheTagManager` wraps Laravel's Cache facade with tag-aware operations. When the driver does not support tags (file, array, database) all operations fall back transparently to plain cache calls.

```php
use PhilipRehberger\CacheToolkit\Facades\CacheTag;

$value = CacheTag::remember(
    ttlKey:   'dashboard_stats',
    callback: fn () => expensiveQuery(),
    tags:     ['clients'],
    'client', '42', 'stats'
);

CacheTag::put($key, $data, ttl: 300, tags: ['clients']);
CacheTag::get($key, tags: ['clients']);
CacheTag::forget($key, tags: ['clients']);
CacheTag::flush(['clients']);
CacheTag::flushType('clients');
```

### Configuration

```php
// config/cache-toolkit.php
return [
    'prefixes' => [],
    'ttl' => [
        'default' => 300,
        'short'   => 60,
        'medium'  => 900,
        'long'    => 3600,
        'daily'   => 86400,
    ],
    'tags' => [],
];
```

## API

### CacheKeyBuilder

| Method | Description | Returns |
|--------|-------------|---------|
| `CacheKeyBuilder::make(string ...$parts)` | Build a key from arbitrary parts (empty parts filtered) | `string` |
| `CacheKeyBuilder::forModel(Model $model, string ...$suffix)` | Build a key from a model instance | `string` |
| `CacheKeyBuilder::forModelType(string $class, int\|string $id, string ...$suffix)` | Build a key from a class name and ID | `string` |
| `CacheKeyBuilder::forList(string $type, array $filters = [])` | Build a list key with optional filter hash | `string` |
| `CacheKeyBuilder::forPaginated(string $type, int $page, int $perPage, array $filters = [])` | Build a paginated list key | `string` |
| `CacheKeyBuilder::forAnalytics(string $type, ?string $from = null, ?string $to = null)` | Build an analytics key | `string` |
| `CacheKeyBuilder::forUser(int $userId, string $type)` | Build a user-scoped key | `string` |
| `CacheKeyBuilder::forDateRange(string $type, string $from, string $to)` | Build a date-range key | `string` |
| `CacheKeyBuilder::ttl(string $key)` | Get TTL in seconds from config | `int` |
| `CacheKeyBuilder::ttlCarbon(string $key)` | Get TTL as Carbon instance | `DateTimeInterface` |
| `CacheKeyBuilder::tags(string $type)` | Get tag array from config | `string[]` |
| `CacheKeyBuilder::supportsTags()` | Check if current driver supports tags | `bool` |

### CacheTagManager

| Method | Description | Returns |
|--------|-------------|---------|
| `->remember(string $ttlKey, callable $callback, array $tags, string ...$keyParts)` | Get or store a value | `mixed` |
| `->put(string $key, mixed $value, int $ttl, array $tags = [])` | Store a value | `void` |
| `->get(string $key, array $tags = [])` | Retrieve a value | `mixed` |
| `->has(string $key, array $tags = [])` | Check existence | `bool` |
| `->forget(string $key, array $tags = [])` | Remove a value | `void` |
| `->flush(array $tags)` | Flush all entries for given tags | `bool` |
| `->flushType(string $type)` | Flush entries for a config-defined type | `bool` |
| `->flushTypes(string[] $types)` | Flush multiple config-defined types | `bool[]` |

### Tag Driver Support

| Driver | Tags Supported | Behaviour |
|--------|---------------|-----------|
| `redis` | Yes | Tags used for grouping and invalidation |
| `memcached` | Yes | Tags used for grouping and invalidation |
| `file` | No | Falls back to plain cache; `flush` returns `false` |
| `array` | No | Falls back to plain cache; `flush` returns `false` |
| `database` | No | Falls back to plain cache; `flush` returns `false` |

## Development

```bash
composer install
vendor/bin/phpunit
vendor/bin/pint --test
vendor/bin/phpstan analyse
```

## Support

If you find this project useful:

⭐ [Star the repo](https://github.com/philiprehberger/laravel-cache-toolkit)

🐛 [Report issues](https://github.com/philiprehberger/laravel-cache-toolkit/issues?q=is%3Aissue+is%3Aopen+label%3Abug)

💡 [Suggest features](https://github.com/philiprehberger/laravel-cache-toolkit/issues?q=is%3Aissue+is%3Aopen+label%3Aenhancement)

❤️ [Sponsor development](https://github.com/sponsors/philiprehberger)

🌐 [All Open Source Projects](https://philiprehberger.com/open-source-packages)

💻 [GitHub Profile](https://github.com/philiprehberger)

🔗 [LinkedIn Profile](https://www.linkedin.com/in/philiprehberger)

## License

[MIT](LICENSE)
