<?php

namespace Anik\Cache\Contracts;

use Psr\Cache\CacheItemInterface as Psr6CacheItemInterface;

interface CacheItemInterface extends Psr6CacheItemInterface
{
    public function getExpiration(): ?int;

    public function getValue(): mixed;
}
