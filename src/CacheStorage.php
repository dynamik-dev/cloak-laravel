<?php

declare(strict_types=1);

namespace DynamikDev\Cloak\Laravel;

use DynamikDev\Cloak\Contracts\StoreInterface;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

class CacheStorage implements StoreInterface
{
    public function __construct(
        protected ?string $store = null,
        protected int $ttl = 3600
    ) {}

    public function put(string $key, array $map): void
    {
        $this->cache()->put($this->prefixKey($key), $map, $this->ttl);
    }

    public function get(string $key): ?array
    {
        return $this->cache()->get($this->prefixKey($key));
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
