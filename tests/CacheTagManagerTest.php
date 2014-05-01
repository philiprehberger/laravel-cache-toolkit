<?php

declare(strict_types=1);

namespace PhilipRehberger\CacheToolkit\Tests;

use Illuminate\Support\Facades\Cache;
use Orchestra\Testbench\TestCase;
use PhilipRehberger\CacheToolkit\CacheTagManager;
use PhilipRehberger\CacheToolkit\CacheToolkitServiceProvider;

class CacheTagManagerTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [CacheToolkitServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('cache.default', 'array');
        $app['config']->set('cache-toolkit.ttl.default', 300);
        $app['config']->set('cache-toolkit.ttl.test_type', 120);
        $app['config']->set('cache-toolkit.tags.clients', ['clients', 'crm']);
        $app['config']->set('cache-toolkit.tags.invoices', ['invoices']);
    }

    private function manager(): CacheTagManager
    {
        return $this->app->make(CacheTagManager::class);
    }

    // -------------------------------------------------------------------------
    // remember
    // -------------------------------------------------------------------------

    public function test_remember_without_tags_uses_plain_cache(): void
    {
        // array driver does not support tags; remember falls back to plain cache
        $manager = $this->manager();

        $value = $manager->remember('test_type', fn () => 'hello', [], 'test', 'key');

        $this->assertSame('hello', $value);
        $this->assertSame('hello', Cache::get('test:key'));
    }

    public function test_remember_with_tags_falls_back_on_non_tag_driver(): void
    {
        // array driver does not support tags → still stores in plain cache
        $manager = $this->manager();

        $value = $manager->remember('test_type', fn () => 'world', ['mytag'], 'tagged', 'key');

        $this->assertSame('world', $value);
    }

    // -------------------------------------------------------------------------
    // put
    // -------------------------------------------------------------------------

    public function test_put_without_tags_uses_plain_cache(): void
    {
        $manager = $this->manager();

        $result = $manager->put('my:key', 'myvalue', 60);

        $this->assertTrue($result);
        $this->assertSame('myvalue', Cache::get('my:key'));
    }

    public function test_put_with_tags_falls_back_on_non_tag_driver(): void
    {
        $manager = $this->manager();

        $result = $manager->put('tagged:key', 'data', 60, ['sometag']);

        $this->assertTrue($result);
    }

    // -------------------------------------------------------------------------
    // get
    // -------------------------------------------------------------------------

    public function test_get_without_tags_uses_plain_cache(): void
    {
        Cache::put('plain:key', 'storedvalue', 60);
        $manager = $this->manager();

        $value = $manager->get('plain:key');

        $this->assertSame('storedvalue', $value);
    }

    public function test_get_returns_null_for_missing_key(): void
    {
        $manager = $this->manager();

        $this->assertNull($manager->get('nonexistent:key'));
    }

    // -------------------------------------------------------------------------
    // has
    // -------------------------------------------------------------------------

    public function test_has_without_tags_uses_plain_cache(): void
    {
        Cache::put('exists:key', true, 60);
        $manager = $this->manager();

        $this->assertTrue($manager->has('exists:key'));
        $this->assertFalse($manager->has('missing:key'));
    }

    // -------------------------------------------------------------------------
    // forget
    // -------------------------------------------------------------------------

    public function test_forget_without_tags_uses_plain_cache(): void
    {
        Cache::put('delete:me', 'value', 60);
        $manager = $this->manager();

        $result = $manager->forget('delete:me');

        $this->assertTrue($result);
        $this->assertFalse(Cache::has('delete:me'));
    }

    // -------------------------------------------------------------------------
    // flush
    // -------------------------------------------------------------------------

    public function test_flush_returns_false_when_tags_not_supported(): void
    {
        // array driver does not support tags
        $manager = $this->manager();

        $result = $manager->flush(['clients']);

        $this->assertFalse($result);
    }

    public function test_flush_type_uses_configured_tags(): void
    {
        // With array driver, flushType returns false (tags not supported)
        $manager = $this->manager();

        $result = $manager->flushType('clients');

        $this->assertFalse($result);
    }

    // -------------------------------------------------------------------------
    // flushTypes
    // -------------------------------------------------------------------------

    public function test_flush_types_processes_all_types(): void
    {
        $manager = $this->manager();

        $results = $manager->flushTypes(['clients', 'invoices']);

        $this->assertArrayHasKey('clients', $results);
        $this->assertArrayHasKey('invoices', $results);
        // Both false because array driver does not support tags
        $this->assertFalse($results['clients']);
        $this->assertFalse($results['invoices']);
    }

    // -------------------------------------------------------------------------
    // supportsTags
    // -------------------------------------------------------------------------

    public function test_supports_tags_delegates_to_key_builder(): void
    {
        $manager = $this->manager();

        // array driver does not support tags
        $this->assertFalse($manager->supportsTags());

        $this->app['config']->set('cache.default', 'redis');
        $this->assertTrue($manager->supportsTags());
    }

    // -------------------------------------------------------------------------
    // getConfiguredTags
    // -------------------------------------------------------------------------

    public function test_get_configured_tags_returns_config(): void
    {
        $manager = $this->manager();

        $tags = $manager->getConfiguredTags();

        $this->assertArrayHasKey('clients', $tags);
        $this->assertArrayHasKey('invoices', $tags);
        $this->assertSame(['clients', 'crm'], $tags['clients']);
    }
}
