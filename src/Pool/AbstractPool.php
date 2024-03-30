<?php

declare(strict_types=1);

namespace Anik\Cache\Pool;

use Anik\Cache\Exception\InvalidArgumentException;
use Psr\Cache\CacheItemPoolInterface;

abstract class AbstractPool implements CacheItemPoolInterface
{
    protected const RESERVED_CHARACTERS = '{}()/\@:';

    protected function validateKey(string $key): string
    {
        $stringLength = mb_strlen($key);

        if ($stringLength <= 0) {
            throw new InvalidArgumentException('Cache key characters must be greater than zero.');
        }

        if (preg_match('#[' . preg_quote(static::RESERVED_CHARACTERS) . ']#', $key) > 0) {
            throw new InvalidArgumentException(
                sprintf(
                    'Cache key "%s" cannot contain reserved characters: "%s".',
                    $key,
                    static::RESERVED_CHARACTERS
                )
            );
        }

        return $key;
    }

    public function getItems(array $keys = []): array
    {
        $results = [];
        foreach ($keys as $key) {
            $results[$key] = $this->getItem($key);
        }

        return $results;
    }

    public function deleteItems(array $keys): bool
    {
        foreach ($keys as $key) {
            $this->deleteItem($key);
        }

        return true;
    }
}
