<?php

declare(strict_types=1);

namespace PhilipRehberger\CacheToolkit;

use Illuminate\Support\ServiceProvider;

class CacheToolkitServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/cache-toolkit.php',
            'cache-toolkit'
        );

        $this->app->singleton(CacheTagManager::class, function () {
            return new CacheTagManager;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/cache-toolkit.php' => config_path('cache-toolkit.php'),
            ], 'cache-toolkit-config');
        }
    }
}
