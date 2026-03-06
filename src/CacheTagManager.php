<?php

declare(strict_types=1);

namespace PhilipRehberger\CacheToolkit;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Manages cache tags for grouped cache invalidation.
 *
 * Only works with Redis/Memcached drivers that support tags.
 * Falls back to no-op for other drivers.
 */
class CacheTagManager
{
    /**
     * Remember a value with tags.
     */
    public function remember(string $ttlKey, callable $callback, array $tags, string ...$keyParts): mixed
    {
        $key = CacheKeyBuilder::make(...$keyParts);
        $ttl = CacheKeyBuilder::ttl($ttlKey);

        if (CacheKeyBuilder::supportsTags() && ! empty($tags)) {
            return Cache::tags($tags)->remember($key, $ttl, $callback);
        }

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Store a value with tags.
     */
    public function put(string $key, mixed $value, int $ttl, array $tags = []): bool
    {
        if (CacheKeyBuilder::supportsTags() && ! empty($tags)) {
            return Cache::tags($tags)->put($key, $value, $ttl);
        }

        return Cache::put($key, $value, $ttl);
    }

    /**
     * Flush all cache entries with given tags.
     */
    public function flush(array $tags): bool
    {
        if (! CacheKeyBuilder::supportsTags()) {
            Log::debug('Cache tags not supported, skipping flush', ['tags' => $tags]);

            return false;
        }

        try {
            Cache::tags($tags)->flush();
            Log::info('Cache flushed by tags', ['tags' => $tags]);

            return true;
        } catch (\Exception $e) {
            Log::warning('Cache tag flush failed', [
                'tags' => $tags,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Flush cache for a specific type using configured tags.
     */
    public function flushType(string $type): bool
    {
        $tags = CacheKeyBuilder::tags($type);

        return $this->flush($tags);
    }

    /**
     * Flush multiple types at once.
     */
    public function flushTypes(array $types): array
    {
        $results = [];

        foreach ($types as $type) {
            $results[$type] = $this->flushType($type);
        }

        return $results;
    }

    /**
     * Get a value from tagged cache.
     */
    public function get(string $key, array $tags = []): mixed
    {
        if (CacheKeyBuilder::supportsTags() && ! empty($tags)) {
            return Cache::tags($tags)->get($key);
        }

        return Cache::get($key);
    }

    /**
     * Check if a key exists in tagged cache.
     */
    public function has(string $key, array $tags = []): bool
    {
        if (CacheKeyBuilder::supportsTags() && ! empty($tags)) {
            return Cache::tags($tags)->has($key);
        }

        return Cache::has($key);
    }

    /**
     * Forget a specific key from tagged cache.
     */
    public function forget(string $key, array $tags = []): bool
    {
        if (CacheKeyBuilder::supportsTags() && ! empty($tags)) {
            return Cache::tags($tags)->forget($key);
        }

        return Cache::forget($key);
    }

    /**
     * Get all configured tag groups.
     */
    public function getConfiguredTags(): array
    {
        return (array) config('cache-toolkit.tags', []);
    }

    /**
     * Check if the current driver supports tags.
     */
    public function supportsTags(): bool
    {
        return CacheKeyBuilder::supportsTags();
    }
}
