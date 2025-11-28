<?php

use DynamikDev\Cloak\Laravel\CacheStorage;
use Illuminate\Support\Facades\Cache;

it('stores values in cache', function () {
    $storage = new CacheStorage;
    $map = ['{{EMAIL_ABC123_1}}' => 'test@example.com'];

    $storage->put('test-key', $map);

    $cached = Cache::get('cloak:test-key');
    expect($cached)->toBeArray();
    expect($cached)->toBe($map);
});

it('retrieves values from cache', function () {
    $storage = new CacheStorage;
    $map = ['{{EMAIL_ABC123_1}}' => 'test@example.com'];

    $storage->put('test-key', $map);
    $retrieved = $storage->get('test-key');

    expect($retrieved)->toBe($map);
});

it('returns null for non-existent keys', function () {
    $storage = new CacheStorage;

    expect($storage->get('non-existent'))->toBeNull();
});

it('forgets cached values', function () {
    $storage = new CacheStorage;
    $storage->put('test-key', ['foo' => 'bar']);

    $storage->forget('test-key');

    expect($storage->get('test-key'))->toBeNull();
});

it('uses specified cache store', function () {
    config(['cache.stores.custom' => ['driver' => 'array']]);
    $storage = new CacheStorage('custom');

    $storage->put('test-key', ['foo' => 'bar']);

    expect(Cache::store('custom')->has('cloak:test-key'))->toBeTrue();
});

it('handles multiple values in map', function () {
    $storage = new CacheStorage;
    $map = [
        '{{EMAIL_ABC123_1}}' => 'test@example.com',
        '{{EMAIL_ABC123_2}}' => 'another@example.com',
        '{{PHONE_ABC123_1}}' => '555-123-4567',
    ];

    $storage->put('multi-key', $map);
    $retrieved = $storage->get('multi-key');

    expect($retrieved)->toBe($map);
});

it('uses custom TTL when specified in constructor', function () {
    $storage = new CacheStorage(null, 7200);
    $storage->put('test-key', ['foo' => 'bar']);

    expect(Cache::get('cloak:test-key'))->toBe(['foo' => 'bar']);
});
