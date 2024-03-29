<?php

declare(strict_types=1);

namespace Anik\Cache\Pool;

use Anik\Cache\Item;
use Psr\Cache\CacheItemInterface;

final class NullPool extends AbstractPool
{
    private bool $defaultReturn;

    public function __construct(bool $defaultReturn = false)
    {
        $this->defaultReturn = $defaultReturn;
    }

    public function getItem(string $key): Item
    {
        $this->validateKey($key);

        return new Item($key, null, null, false);
    }

    public function hasItem(string $key): bool
    {
        $this->validateKey($key);

        return false;
    }

    public function clear(): bool
    {
        return true;
    }

    public function deleteItem(string $key): bool
    {
        $this->validateKey($key);

        return true;
    }

    public function save(CacheItemInterface $item): bool
    {
        $this->validateKey($item->getKey());

        return $this->defaultReturn;
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        $this->validateKey($item->getKey());

        return $this->defaultReturn;
    }

    public function commit(): bool
    {
        return $this->defaultReturn;
    }
}
