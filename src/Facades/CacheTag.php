<?php

declare(strict_types=1);

namespace PhilipRehberger\CacheToolkit\Facades;

use Illuminate\Support\Facades\Facade;
use PhilipRehberger\CacheToolkit\CacheTagManager;

/**
 * Facade for CacheTagManager.
 *
 * @method static mixed remember(string $ttlKey, callable $callback, array $tags, string ...$keyParts)
 * @method static bool put(string $key, mixed $value, int $ttl, array $tags = [])
 * @method static bool flush(array $tags)
 * @method static bool flushType(string $type)
 * @method static array flushTypes(array $types)
 * @method static mixed get(string $key, array $tags = [])
 * @method static bool has(string $key, array $tags = [])
 * @method static bool forget(string $key, array $tags = [])
 * @method static array getConfiguredTags()
 * @method static bool supportsTags()
 *
 * @see CacheTagManager
 */
class CacheTag extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return CacheTagManager::class;
    }
}
