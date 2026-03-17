<?php

declare(strict_types=1);

namespace PhilipRehberger\CacheToolkit\Facades;

use Illuminate\Support\Facades\Facade;
use PhilipRehberger\CacheToolkit\CacheKeyBuilder;

/**
 * Facade for CacheKeyBuilder.
 *
 * @method static string make(string ...$parts)
 * @method static string forModel(\Illuminate\Database\Eloquent\Model $model, string $suffix = '')
 * @method static string forModelType(string $modelClass, int|string $id, string $suffix = '')
 * @method static string forList(string $type, array $filters = [])
 * @method static string forAnalytics(string $type, ?string $startDate = null, ?string $endDate = null)
 * @method static string forUser(int $userId, string $type)
 * @method static string forDateRange(string $prefix, string $from, string $to)
 * @method static string forPaginated(string $prefix, int $page, int $perPage, array $filters = [])
 * @method static int ttl(string $type)
 * @method static \DateTimeInterface ttlCarbon(string $type)
 * @method static array tags(string $type)
 * @method static string prefix(string $type)
 * @method static bool supportsTags()
 *
 * @see CacheKeyBuilder
 */
class CacheKey extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return CacheKeyBuilder::class;
    }
}
