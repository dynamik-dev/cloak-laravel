<?php

declare(strict_types=1);

namespace DynamikDev\Cloak\Laravel;

use DynamikDev\Cloak\Contracts\StoreInterface;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class CacheStorage implements StoreInterface
{
    public function __construct(
        protected ?string $store = null
    ) {}

    public function put(string $key, array $map, int $ttl = 3600): void
    {
        $encryptedMap = [];
        foreach ($map as $placeholder => $originalValue) {
            $encryptedMap[$placeholder] = Crypt::encryptString($originalValue);
        }

        $this->cache()->put($this->prefixKey($key), $encryptedMap, $ttl);
    }

    public function get(string $key): ?array
    {
        $encryptedMap = $this->cache()->get($this->prefixKey($key));

        if ($encryptedMap === null) {
            return null;
        }

        $decryptedMap = [];
        foreach ($encryptedMap as $placeholder => $encryptedValue) {
            $decryptedMap[$placeholder] = Crypt::decryptString($encryptedValue);
        }

        return $decryptedMap;
    }

    public function forget(string $key): void
    {
        $this->cache()->forget($this->prefixKey($key));
    }

    protected function cache(): Repository
    {
        return Cache::store($this->store);
    }

    protected function prefixKey(string $key): string
    {
        return 'cloak:'.$key;
    }
}
