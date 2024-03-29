anik/cache
[![codecov](https://codecov.io/gh/ssi-anik/cache/graph/badge.svg?token=OU7XRFJQYH)](https://codecov.io/gh/ssi-anik/cache)
[![PHP Version Require](http://poser.pugx.org/anik/cache/require/php)](//packagist.org/packages/anik/cache)
[![Latest Stable Version](https://poser.pugx.org/anik/cache/v)](//packagist.org/packages/anik/cache)
===

[anik/cache](https://packagist.org/packages/anik/cache) contains the implementation
of [PSR-6 - Caching Interface](https://www.php-fig.org/psr/psr-6/) & [PSR-16 - Common Interface for Caching Libraries](https://www.php-fig.org/psr/psr-16/)
with

- Null Cache
- In-Memory/Runtime/Array cache

## Use-case

- When consuming a library that requires psr cache, and you don't want to use cache for some reason.
- When developing a library and developer may not provide the cache, and you don't want to do the if...else check.

```php

if (!is_null($this->cache)){
    $this->cache->set($key,$value,$ttl);
}

if (!is_null($this->cache)){
    $this->cache->get($key)
}

```

# Documentation

## Installation

To install the package, run
> composer require anik/cache

## Usage

### InMemoryPool & NullPool

All the ***Pool** classes implement `CacheItemPoolInterface` interface defined in **PSR-6**. So, the following methods
are available

```php
use Psr\Cache\CacheItemInterface;

public function getItem(string $key): CacheItemInterface;
public function getItems(array $keys): CacheItemInterface[];
public function hasItem(string $key): bool;
public function clear(): bool;
public function deleteItem(string $key): bool;
public function deleteItems(array $keys): bool;
public function save(CacheItemInterface $item): bool;
public function saveDeferred(CacheItemInterface $item): bool;
public function commit(): bool;
```

### Item

The item class implements `\Anik\Cache\Contracts\CacheItemInterface`, an extension of `\Psr\Cache\CacheItemInterface`.
So, the following methods are available.

```php
public function getKey(): string;
public function get(): mixed;
public function isHit(): bool;
public function set(mixed $value): static;
public function expiresAt(\DateTimeInterface|null $expiration): static;
public function expiresAfter(\DateInterval|int|null$time): static;

public function getExpiration(): ?int;
public function getValue(): mixed;
```

#### Caveat

When saving data to the cache using **save** or **saveDeferred** methods, the `\Anik\Cache\Contracts\CacheItemInterface`
should be passed to those methods.

### PoolAdapter

The **PoolAdapter** class implements both `CacheItemPoolInterface` (PSR-6), `CacheInterface` (PSR-16) interfaces. So the
following methods are available.

```php
use Psr\Cache\CacheItemInterface;

public function getItem(string $key): CacheItemInterface;
public function getItems(array $keys): CacheItemInterface[];
public function hasItem(string $key): bool;
public function clear(): bool;
public function deleteItem(string $key): bool;
public function deleteItems(array $keys): bool;
public function save(CacheItemInterface $item): bool;
public function saveDeferred(CacheItemInterface $item): bool;
public function commit(): bool;

public function get(string $key, mixed $default = null): mixed;
public function set(string $key, mixed $value, \DateInterval|int|null $ttl = null): bool;
public function delete(string $key): bool;
public function clear(): bool;
public function getMultiple(iterable $keys, mixed $default = null): iterable;
public function setMultiple(iterable $values, \DateInterval|int|null $ttl = null): bool;
public function deleteMultiple(iterable $keys): bool;
public function has(string $key): bool;
```

### Example

### NullPool/InMemoryPool

```php
use Anik\Cache\Item;
use Anik\Cache\Pool\NullPool;

$pool = new NullPool();
// $pool = new NullPool($defaultReturnValue);
// $pool = new InMemoryPool();

// Item to store
$item = new Item('key-1', 'value-1');
// $item = new Item('key-2', 'value-2');

// Item expiration
// $item->expiresAfter(10);
// $item->expiresAt(($now = new DateTimeImmutable())->modify('+100 seconds'))

// save item
$pool->save($item);

// save deferred
$pool->saveDeferred($item);
$pool->commit();

// get item
$item = $pool->getItem('key-1');
$item->isHit();
$item->get();

// get multiple items
$pool->getItems(['key-1', 'key-2']);

// has item
$pool->hasItem('key-1');

// delete item
$pool->deleteItem('key-1');

// delete multiple items
$pool->deleteItems(['key-1', 'key-2']);

// clear pool
$pool->clear();
```

### PoolAdapter

```php
use Anik\Cache\Item;
use Anik\Cache\Pool\NullPool;
use Anik\Cache\PoolAdapter;

// pass the type of pool you want to use
$adapter = new PoolAdapter(new NullPool());
// $adapter = new PoolAdapter(new NullPool($defaultReturnValue));
// $adapter = new PoolAdapter(new InMemoryPool());

// can achieve the same using the helper methods
// $adapter = null_cache();
// $adapter = null_cache($defaultReturnValue);
// $adapter = in_memory_cache();

// Item to store
$item = new Item('key-1', 'value-1');
// $item = new Item('key-2', 'value-2');

// Item expiration
// $item->expiresAfter(10);
// $item->expiresAt(($now = new DateTimeImmutable())->modify('+100 seconds'))

// save/save deferred item
$adapter->save($item);
$adapter->saveDeferred($item);
$adapter->commit();
// Otherwise,
$adapter->set('key-3', 'value-3');

// get item
$item = $adapter->getItem('key-1');
$item->isHit();
$item->get();
// Otherwise
$adapter->get('key-3');

// get multiple items
$adapter->getItems(['key-1', 'key-2']);

// Otherwise
$adapter->getMultiple(['key-1', 'key-4'], 'default-value');

// has item
$adapter->hasItem('key-1');
// otherwise
$adapter->has('key-1');

// delete item
$adapter->deleteItem('key-1');

// delete multiple items
$adapter->deleteItems(['key-1', 'key-2']);
// Otherwise
$adapter->deleteMultiple(['key-1', 'key-2']);

// clear pool
$adapter->clear();
```
