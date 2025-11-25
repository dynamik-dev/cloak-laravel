<?php

declare(strict_types=1);

namespace DynamikDev\Cloak\Laravel;

use DynamikDev\Cloak\Cloak;
use DynamikDev\Cloak\Contracts\StoreInterface;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CloakServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('cloak')
            ->hasConfigFile();
    }

    public function packageRegistered(): void
    {
        $this->app->bind(StoreInterface::class, function ($app) {
            $persist = config('cloak.persist', false);

            if (! $persist) {
                return new EncryptedArrayStorage;
            }

            $storageClass = config('cloak.storage_driver', CacheStorage::class);
            $cacheStore = config('cloak.cache_store');

            return new $storageClass($cacheStore);
        });

        $this->app->singleton(Cloak::class, function ($app) {
            return Cloak::using($app->make(StoreInterface::class));
        });

        $this->app->alias(Cloak::class, 'cloak');
    }
}
