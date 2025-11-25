<?php

use DynamikDev\Cloak\Laravel\EncryptedArrayStorage;
use Illuminate\Support\Facades\Crypt;

it('stores encrypted values in memory', function () {
    $storage = new EncryptedArrayStorage;
    $map = ['{{EMAIL_ABC123_1}}' => 'test@example.com'];

    $storage->put('test-key', $map, 3600);

    // Access parent's protected data via reflection to verify encryption
    $reflection = new ReflectionClass($storage);
    $property = $reflection->getProperty('data');
    $property->setAccessible(true);
    $data = $property->getValue($storage);

    expect($data['test-key']['{{EMAIL_ABC123_1}}'])->not->toBe('test@example.com');
    expect(Crypt::decryptString($data['test-key']['{{EMAIL_ABC123_1}}']))->toBe('test@example.com');
});

it('retrieves and decrypts values', function () {
    $storage = new EncryptedArrayStorage;
    $map = ['{{EMAIL_ABC123_1}}' => 'test@example.com'];

    $storage->put('test-key', $map, 3600);
    $retrieved = $storage->get('test-key');

    expect($retrieved)->toBe($map);
});

it('returns null for non-existent keys', function () {
    $storage = new EncryptedArrayStorage;

    expect($storage->get('non-existent'))->toBeNull();
});

it('forgets values', function () {
    $storage = new EncryptedArrayStorage;
    $storage->put('test-key', ['foo' => 'bar'], 3600);

    $storage->forget('test-key');

    expect($storage->get('test-key'))->toBeNull();
});

it('handles multiple values in map', function () {
    $storage = new EncryptedArrayStorage;
    $map = [
        '{{EMAIL_ABC123_1}}' => 'test@example.com',
        '{{EMAIL_ABC123_2}}' => 'another@example.com',
        '{{PHONE_ABC123_1}}' => '555-123-4567',
    ];

    $storage->put('multi-key', $map, 3600);
    $retrieved = $storage->get('multi-key');

    expect($retrieved)->toBe($map);
});

it('extends ArrayStore', function () {
    $storage = new EncryptedArrayStorage;

    expect($storage)->toBeInstanceOf(\DynamikDev\Cloak\Stores\ArrayStore::class);
});
