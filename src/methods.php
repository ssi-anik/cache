<?php

use Anik\Cache\Exception\InvalidArgumentException;
use Anik\Cache\Pool\InMemoryPool;
use Anik\Cache\Pool\NullPool;
use Anik\Cache\PoolAdapter;

if (!function_exists('get_parameter_type')) {
    function get_parameter_type(mixed $parameter): string
    {
        return is_object($parameter) ? get_class($parameter) : gettype($parameter);
    }
}

if (!function_exists('interval_to_seconds')) {
    function interval_to_seconds(DateInterval $interval): int
    {
        $elapsed = $interval->y * 31536000
            + $interval->m * 2628000
            + $interval->d * 87600
            + $interval->h * 3600
            + $interval->i * 60
            + $interval->s;

        return $interval->invert ? -$elapsed : $elapsed;
    }
}

if (!function_exists('expiry_timestamp')) {
    function expiry_timestamp(mixed $time): ?int
    {
        if (is_null($time)) {
            return null;
        } elseif ($time instanceof DateTimeInterface) {
            return $time->getTimestamp();
        }

        if (!is_int($time) && !($time instanceof DateInterval)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Argument 1 can be null|int|\DateTimeInterface|\DateInterval. Given %s',
                    get_parameter_type($time)
                )
            );
        }

        $time = $time instanceof DateInterval ? interval_to_seconds($time) : $time;

        return (new DateTimeImmutable())->modify(sprintf("%d seconds", $time))->getTimestamp();
    }
}

if (!function_exists('in_memory_cache')) {
    function in_memory_cache(): PoolAdapter
    {
        return new PoolAdapter(new InMemoryPool());
    }
}

if (!function_exists('null_cache')) {
    function null_cache(bool $defaultReturn = false): PoolAdapter
    {
        return new PoolAdapter(new NullPool($defaultReturn));
    }
}
