<?php

declare(strict_types=1);

namespace Anik\Cache\Pool;

use Anik\Cache\Contracts\CacheItemInterface as ExtendedCacheItemInterface;
use Anik\Cache\Item;
use DateTimeImmutable;
use Psr\Cache\CacheItemInterface;

final class InMemoryPool extends AbstractPool
{
    private const VALUE = 'value';
    private const EXPIRES_AT = 'expires_at';

    private $items = [];
    private $deferredItems = [];

    private function now(): DateTimeImmutable
    {
        return (new DateTimeImmutable());
    }

    private function itemByKey($key)
    {
        $this->validateKey($key);

        return $this->items[$key] ?? null;
    }

    public function getItem($key): Item
    {
        $item = $this->itemByKey($key);

        if (is_null($item)) {
            return new Item($key, null, null, false);
        }

        if (!is_null($item[self::EXPIRES_AT]) && $this->now()->getTimestamp() > $item[self::EXPIRES_AT]) {
            $this->deleteItem($key);

            return new Item($key, null, null, false);
        }

        $expiresAt = isset($item[self::EXPIRES_AT])
            ? (new DateTimeImmutable())->setTimestamp($item[self::EXPIRES_AT])
            : null;

        return new Item(
            $key,
            $item[self::VALUE],
            $expiresAt,
            true
        );
    }

    public function hasItem($key): bool
    {
        return $this->getItem($key)->isHit();
    }

    public function clear(): bool
    {
        $this->items = [];
        $this->deferredItems = [];

        return true;
    }

    public function deleteItem($key): bool
    {
        $this->validateKey($key);

        unset($this->items[$key]);

        return true;
    }

    public function save(CacheItemInterface $item): bool
    {
        if (!$item instanceof ExtendedCacheItemInterface) {
            return false;
        }

        $this->validateKey($key = $item->getKey());
        $this->items[$key] = [
            self::VALUE => $item->getValue(),
            self::EXPIRES_AT => $item->getExpiration(),
        ];

        return true;
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        $this->validateKey($item->getKey());
        $this->deferredItems = array_merge($this->deferredItems, [$item]);

        return true;
    }

    public function commit(): bool
    {
        foreach ($this->deferredItems as $deferredItem) {
            $this->save($deferredItem);
        }

        $this->deferredItems = [];

        return true;
    }
}
