<?php

declare(strict_types=1);

namespace PhilipRehberger\CacheToolkit;

use Illuminate\Database\Eloquent\Model;

/**
 * Standardized cache key builder for consistent key naming.
 *
 * Usage:
 *   CacheKeyBuilder::make('client', '123', 'stats')       // "client:123:stats"
 *   CacheKeyBuilder::forModel($client, 'details')        // "client:123:details"
 *   CacheKeyBuilder::forList('clients', ['active' => 1]) // "client:list:abc123"
 *   CacheKeyBuilder::ttl('dashboard_stats')              // 300
 */
class CacheKeyBuilder
{
    private const SEPARATOR = ':';

    /**
     * Build a cache key from parts.
     */
    public static function make(string ...$parts): string
    {
        return implode(self::SEPARATOR, array_filter($parts, fn ($part) => $part !== '' && $part !== null));
    }

    /**
     * Build a key for a model instance.
     */
    public static function forModel(Model $model, string $suffix = ''): string
    {
        $prefix = self::getModelPrefix($model);
        $key = self::make($prefix, (string) $model->getKey());

        return $suffix ? self::make($key, $suffix) : $key;
    }

    /**
     * Build a key for a model type with ID.
     */
    public static function forModelType(string $modelClass, int|string $id, string $suffix = ''): string
    {
        $prefix = self::getClassPrefix($modelClass);
        $key = self::make($prefix, (string) $id);

        return $suffix ? self::make($key, $suffix) : $key;
    }

    /**
     * Build a key for a list/collection with optional filters.
     */
    public static function forList(string $type, array $filters = []): string
    {
        $prefix = config("cache-toolkit.prefixes.{$type}", $type);
        $filterHash = $filters ? md5(serialize($filters)) : 'all';

        return self::make($prefix, 'list', $filterHash);
    }

    /**
     * Build a key for analytics data.
     */
    public static function forAnalytics(string $type, ?string $startDate = null, ?string $endDate = null): string
    {
        return self::make('analytics', $type, $startDate ?? 'all', $endDate ?? 'all');
    }

    /**
     * Build a key for user-specific data.
     */
    public static function forUser(int $userId, string $type): string
    {
        return self::make('user', (string) $userId, $type);
    }

    /**
     * Build a key for date-ranged data.
     */
    public static function forDateRange(string $prefix, string $from, string $to): string
    {
        return self::make($prefix, $from, $to);
    }

    /**
     * Build a key for paginated data.
     */
    public static function forPaginated(string $prefix, int $page, int $perPage, array $filters = []): string
    {
        $filterHash = $filters ? md5(serialize($filters)) : 'all';

        return self::make($prefix, 'page', (string) $page, (string) $perPage, $filterHash);
    }

    /**
     * Get TTL from config in seconds.
     */
    public static function ttl(string $type): int
    {
        return (int) config("cache-toolkit.ttl.{$type}", config('cache-toolkit.ttl.default', 300));
    }

    /**
     * Get TTL from config as Carbon interval.
     */
    public static function ttlCarbon(string $type): \DateTimeInterface
    {
        return now()->addSeconds(self::ttl($type));
    }

    /**
     * Get tags for a cache type.
     */
    public static function tags(string $type): array
    {
        return (array) config("cache-toolkit.tags.{$type}", [$type]);
    }

    /**
     * Get prefix for a type.
     */
    public static function prefix(string $type): string
    {
        return (string) config("cache-toolkit.prefixes.{$type}", $type);
    }

    /**
     * Check if cache driver supports tags.
     */
    public static function supportsTags(): bool
    {
        $driver = config('cache.default');

        return in_array($driver, ['redis', 'memcached'], true);
    }

    /**
     * Get model prefix from instance.
     */
    private static function getModelPrefix(Model $model): string
    {
        return self::getClassPrefix(get_class($model));
    }

    /**
     * Get prefix from class name.
     */
    private static function getClassPrefix(string $class): string
    {
        $baseName = class_basename($class);
        $snake = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $baseName));

        return (string) config("cache-toolkit.prefixes.{$snake}", $snake);
    }
}
