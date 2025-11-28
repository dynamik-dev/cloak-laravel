<?php

use DynamikDev\Cloak\Cloak;
use DynamikDev\Cloak\Contracts\EncryptorInterface;
use DynamikDev\Cloak\Contracts\StoreInterface;
use DynamikDev\Cloak\Laravel\CacheStorage;
use DynamikDev\Cloak\Laravel\Encryptors\LaravelEncryptor;
use DynamikDev\Cloak\Stores\ArrayStore;

it('uses ArrayStore by default (persist: false)', function () {
    $store = app(StoreInterface::class);

    expect($store)->toBeInstanceOf(ArrayStore::class);
});

it('uses CacheStorage when persist is true', function () {
    config(['cloak.persist' => true]);

    $this->app->forgetInstance(StoreInterface::class);
    $store = app(StoreInterface::class);

    expect($store)->toBeInstanceOf(CacheStorage::class);
});

it('creates fresh Cloak instances on each resolution', function () {
    $cloak1 = app(Cloak::class);
    $cloak2 = app(Cloak::class);

    // Should be different instances (not singleton)
    expect($cloak1)->not->toBe($cloak2);
});

it('shares the same store instance across Cloak instances', function () {
    $store1 = app(StoreInterface::class);
    $store2 = app(StoreInterface::class);

    // Store should be singleton
    expect($store1)->toBe($store2);
});

it('resolves cloak alias', function () {
    $cloak = app('cloak');

    expect($cloak)->toBeInstanceOf(Cloak::class);
});

it('uses configured cache store when persist is true', function () {
    config([
        'cloak.persist' => true,
        'cloak.cache_store' => 'array',
    ]);

    $this->app->forgetInstance(StoreInterface::class);
    $store = app(StoreInterface::class);

    expect($store)->toBeInstanceOf(CacheStorage::class);
});

it('registers LaravelEncryptor as singleton', function () {
    $encryptor = app(EncryptorInterface::class);

    expect($encryptor)->toBeInstanceOf(LaravelEncryptor::class);
});

it('Cloak instance uses LaravelEncryptor', function () {
    $cloak = app(Cloak::class);
    $text = 'Email: test@example.com';

    // Cloak and uncloak should work with encryption
    $cloaked = $cloak->cloak($text);
    $uncloaked = $cloak->uncloak($cloaked);

    expect($cloaked)->not->toBe($text);
    expect($uncloaked)->toBe($text);
});

it('resolver is configured to use container', function () {
    // Cloak::make() should use our resolver to get instances from the container
    $instance1 = \DynamikDev\Cloak\Cloak::make();
    $instance2 = \DynamikDev\Cloak\Cloak::make();

    // Should get fresh instances
    expect($instance1)->not->toBe($instance2);

    // Both should have the LaravelEncryptor configured
    $text = 'test@example.com';
    $cloaked1 = $instance1->cloak($text);
    $cloaked2 = $instance2->cloak($text);

    expect($instance1->uncloak($cloaked1))->toBe($text);
    expect($instance2->uncloak($cloaked2))->toBe($text);
});
