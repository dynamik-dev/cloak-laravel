<?php

use DynamikDev\Cloak\Cloak;
use DynamikDev\Cloak\Contracts\StoreInterface;
use DynamikDev\Cloak\Laravel\CacheStorage;
use DynamikDev\Cloak\Laravel\EncryptedArrayStorage;

it('uses EncryptedArrayStorage by default (persist: false)', function () {
    $store = app(StoreInterface::class);

    expect($store)->toBeInstanceOf(EncryptedArrayStorage::class);
});

it('uses CacheStorage when persist is true', function () {
    config(['cloak.persist' => true]);

    $this->app->forgetInstance(StoreInterface::class);
    $store = app(StoreInterface::class);

    expect($store)->toBeInstanceOf(CacheStorage::class);
});

it('registers Cloak as singleton', function () {
    $cloak1 = app(Cloak::class);
    $cloak2 = app(Cloak::class);

    expect($cloak1)->toBe($cloak2);
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
