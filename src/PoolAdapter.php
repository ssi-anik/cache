<?php

declare(strict_types=1);

namespace Anik\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;

class PoolAdapter implements CacheItemPoolInterface, CacheInterface
{
    protected $pool;

    public function __construct(CacheItemPoolInterface $pool)
    {
        $this->pool = $pool;
    }

    public function getItem($key): CacheItemInterface
    {
        return $this->pool->getItem($key);
    }

    public function getItems(array $keys = []): array
    {
        return $this->pool->getItems($keys);
    }

    public function hasItem($key): bool
    {
        return $this->pool->hasItem($key);
    }

    public function clear(): bool
    {
        return $this->pool->clear();
    }

    public function deleteItem($key): bool
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

    public function get($key, $default = null)
    {
        return $this->getItem($key)->get() ?? $default;
    }

    public function set($key, $value, $ttl = null): bool
    {
        return $this->save(new Item($key, $value, $ttl));
    }

    public function delete($key): bool
    {
        return $this->deleteItem($key);
    }

    public function getMultiple($keys, $default = null): array
    {
        $items = $this->getItems($keys);

        $results = [];
        foreach ($items as $key => $item) {
            $results[$key] = $item->get() ?? $default;
        }

        return $results;
    }

    public function setMultiple($values, $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }

        return true;
    }

    public function deleteMultiple($keys): bool
    {
        return $this->deleteItems($keys);
    }

    public function has($key): bool
    {
        return $this->hasItem($key);
    }
}
