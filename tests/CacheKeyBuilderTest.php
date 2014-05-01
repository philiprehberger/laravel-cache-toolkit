<?php

declare(strict_types=1);

namespace PhilipRehberger\CacheToolkit\Tests;

use Illuminate\Database\Eloquent\Model;
use Orchestra\Testbench\TestCase;
use PhilipRehberger\CacheToolkit\CacheKeyBuilder;
use PhilipRehberger\CacheToolkit\CacheToolkitServiceProvider;

class CacheKeyBuilderTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [CacheToolkitServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('cache.default', 'file');
        $app['config']->set('cache-toolkit.ttl.default', 300);
        $app['config']->set('cache-toolkit.ttl.dashboard_stats', 600);
        $app['config']->set('cache-toolkit.tags.clients', ['clients', 'crm']);
        $app['config']->set('cache-toolkit.prefixes', []);
    }

    public function test_make_joins_parts_with_separator(): void
    {
        $key = CacheKeyBuilder::make('client', '123', 'stats');

        $this->assertSame('client:123:stats', $key);
    }

    public function test_make_filters_empty_parts(): void
    {
        $key = CacheKeyBuilder::make('client', '', '123');

        $this->assertSame('client:123', $key);
    }

    public function test_for_model_generates_key(): void
    {
        $model = new class extends Model
        {
            protected $table = 'clients';

            public function getKey(): mixed
            {
                return 42;
            }
        };

        $key = CacheKeyBuilder::forModel($model);

        // Anonymous class basenames vary; assert format: <something>:42
        $this->assertStringEndsWith(':42', $key);
    }

    public function test_for_model_with_suffix(): void
    {
        $model = new class extends Model
        {
            protected $table = 'clients';

            public function getKey(): mixed
            {
                return 7;
            }
        };

        $key = CacheKeyBuilder::forModel($model, 'details');

        $this->assertStringEndsWith(':7:details', $key);
    }

    public function test_for_model_type_generates_key(): void
    {
        $key = CacheKeyBuilder::forModelType('App\\Models\\Client', 99);

        $this->assertSame('client:99', $key);
    }

    public function test_for_model_type_with_suffix(): void
    {
        $key = CacheKeyBuilder::forModelType('App\\Models\\Client', 99, 'invoices');

        $this->assertSame('client:99:invoices', $key);
    }

    public function test_for_list_without_filters(): void
    {
        $key = CacheKeyBuilder::forList('clients');

        $this->assertSame('clients:list:all', $key);
    }

    public function test_for_list_with_filters_generates_hash(): void
    {
        $filters = ['active' => 1, 'stage' => 'prospect'];
        $key = CacheKeyBuilder::forList('clients', $filters);
        $expectedHash = md5(serialize($filters));

        $this->assertSame("clients:list:{$expectedHash}", $key);
    }

    public function test_for_analytics_with_dates(): void
    {
        $key = CacheKeyBuilder::forAnalytics('revenue', '2026-01-01', '2026-03-31');

        $this->assertSame('analytics:revenue:2026-01-01:2026-03-31', $key);
    }

    public function test_for_analytics_without_dates(): void
    {
        $key = CacheKeyBuilder::forAnalytics('revenue');

        $this->assertSame('analytics:revenue:all:all', $key);
    }

    public function test_for_user_generates_key(): void
    {
        $key = CacheKeyBuilder::forUser(5, 'dashboard');

        $this->assertSame('user:5:dashboard', $key);
    }

    public function test_for_date_range_generates_key(): void
    {
        $key = CacheKeyBuilder::forDateRange('reports', '2026-01-01', '2026-01-31');

        $this->assertSame('reports:2026-01-01:2026-01-31', $key);
    }

    public function test_for_paginated_generates_key(): void
    {
        $key = CacheKeyBuilder::forPaginated('clients', 2, 15);

        $this->assertSame('clients:page:2:15:all', $key);
    }

    public function test_for_paginated_with_filters_generates_hash(): void
    {
        $filters = ['status' => 'active'];
        $key = CacheKeyBuilder::forPaginated('clients', 1, 10, $filters);
        $expectedHash = md5(serialize($filters));

        $this->assertSame("clients:page:1:10:{$expectedHash}", $key);
    }

    public function test_ttl_returns_configured_value(): void
    {
        $ttl = CacheKeyBuilder::ttl('dashboard_stats');

        $this->assertSame(600, $ttl);
    }

    public function test_ttl_returns_default_when_not_configured(): void
    {
        $ttl = CacheKeyBuilder::ttl('unknown_type');

        $this->assertSame(300, $ttl);
    }

    public function test_tags_returns_configured_value(): void
    {
        $tags = CacheKeyBuilder::tags('clients');

        $this->assertSame(['clients', 'crm'], $tags);
    }

    public function test_tags_returns_type_as_default_when_not_configured(): void
    {
        $tags = CacheKeyBuilder::tags('invoices');

        $this->assertSame(['invoices'], $tags);
    }

    public function test_supports_tags_returns_false_for_file_driver(): void
    {
        $this->app['config']->set('cache.default', 'file');

        $this->assertFalse(CacheKeyBuilder::supportsTags());
    }

    public function test_supports_tags_returns_true_for_redis_driver(): void
    {
        $this->app['config']->set('cache.default', 'redis');

        $this->assertTrue(CacheKeyBuilder::supportsTags());
    }

    public function test_supports_tags_returns_true_for_memcached_driver(): void
    {
        $this->app['config']->set('cache.default', 'memcached');

        $this->assertTrue(CacheKeyBuilder::supportsTags());
    }
}
