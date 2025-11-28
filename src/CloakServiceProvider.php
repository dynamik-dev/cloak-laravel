<?php

declare(strict_types=1);

namespace DynamikDev\Cloak\Laravel;

use DynamikDev\Cloak\Cloak;
use DynamikDev\Cloak\Contracts\EncryptorInterface;
use DynamikDev\Cloak\Contracts\StoreInterface;
use DynamikDev\Cloak\Laravel\Encryptors\LaravelEncryptor;
use DynamikDev\Cloak\Stores\ArrayStore;
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
        // Register the encryptor as singleton (stateless service)
        $this->app->singleton(EncryptorInterface::class, LaravelEncryptor::class);

        // Register the store as singleton (shared storage for placeholder mappings)
        $this->app->singleton(StoreInterface::class, function ($app) {
            $persist = config('cloak.persist', false);

            if (! $persist) {
                return new ArrayStore;
            }

            $storageClass = config('cloak.storage_driver', CacheStorage::class);
            $cacheStore = config('cloak.cache_store');
            $ttl = config('cloak.default_ttl', 3600);

            return new $storageClass($cacheStore, $ttl);
        });

        // Use bind() for Cloak - creates fresh instance on every resolution
        // This prevents state pollution (filters, callbacks, etc.) especially in Octane
        $this->app->bind(Cloak::class, function ($app) {
            return Cloak::using($app->make(StoreInterface::class))
                ->withEncryptor($app->make(EncryptorInterface::class));
        });

        // Set resolver to use container resolution for helper functions
        // This ensures cloak()/uncloak() helpers get fresh instances from the container
        Cloak::resolveUsing(fn () => app(Cloak::class));

        $this->app->alias(Cloak::class, 'cloak');
    }
}
