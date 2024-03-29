<?php

declare(strict_types=1);

namespace Anik\Cache;

use DateInterval;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;

class PoolAdapter implements CacheItemPoolInterface, CacheInterface
{
    protected CacheItemPoolInterface $pool;

    public function __construct(CacheItemPoolInterface $pool)
    {
        $this->pool = $pool;
    }

    public function getItem(string $key): CacheItemInterface
    {
        return $this->pool->getItem($key);
    }

    public function getItems(array $keys = []): array
    {
        return $this->pool->getItems($keys);
    }

    public function hasItem(string $key): bool
    {
        return $this->pool->hasItem($key);
    }

    public function clear(): bool
    {
        return $this->pool->clear();
    }

    public function deleteItem(string $key): bool
    {
        return $this->pool->deleteItem($key);
    }

    public function deleteItems(array $keys): bool
    {
        return $this->pool->deleteItems($keys);
    }

    public function save(CacheItemInterface $item): bool
    {
        return $this->pool->save($item);
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        return $this->pool->saveDeferred($item);
    }

    public function commit(): bool
    {
        return $this->pool->commit();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->getItem($key)->get() ?? $default;
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        return $this->save(new Item($key, $value, $ttl));
    }

    public function delete(string $key): bool
    {
        return $this->deleteItem($key);
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $items = $this->getItems($keys);

        $results = [];
        foreach ($items as $key => $item) {
            $results[$key] = $item->isHit() ? $item->get() : $default;
        }

        return $results;
    }

    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }

        return true;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        return $this->deleteItems($keys);
    }

    public function has(string $key): bool
    {
        return $this->hasItem($key);
    }
}
