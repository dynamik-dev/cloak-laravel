<?php

declare(strict_types=1);

namespace DynamikDev\Cloak\Laravel;

use DynamikDev\Cloak\Stores\ArrayStore;
use Illuminate\Support\Facades\Crypt;

class EncryptedArrayStorage extends ArrayStore
{
    public function put(string $key, array $map, int $ttl = 3600): void
    {
        $encryptedMap = [];
        foreach ($map as $placeholder => $originalValue) {
            $encryptedMap[$placeholder] = Crypt::encryptString($originalValue);
        }

        parent::put($key, $encryptedMap, $ttl);
    }

    public function get(string $key): ?array
    {
        $encryptedMap = parent::get($key);

        if ($encryptedMap === null) {
            return null;
        }

        $decryptedMap = [];
        foreach ($encryptedMap as $placeholder => $encryptedValue) {
            $decryptedMap[$placeholder] = Crypt::decryptString($encryptedValue);
        }

        return $decryptedMap;
    }
}
