<?php

declare(strict_types=1);

namespace Anik\Cache;

use Anik\Cache\Contracts\CacheItemInterface;
use Anik\Cache\Exception\InvalidArgumentException;
use DateInterval;
use DateTimeInterface;

final class Item implements CacheItemInterface
{
    protected string $key;
    protected mixed $value;
    protected ?int $expiresAt;
    protected bool $isHit;

    public function __construct(
        string $key,
        mixed $value = null,
        null|int|DateInterval|DateTimeInterface $expiresAt = null,
        bool $isHit = false
    ) {
        $this->key = $key;
        $this->value = $value;
        $this->expiresAt = expiry_timestamp($expiresAt);
        $this->isHit = $isHit;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function get(): mixed
    {
        if (false === $this->isHit) {
            return null;
        }

        return $this->value;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function isHit(): bool
    {
        return $this->isHit;
    }

    public function set($value): static
    {
        if (
            !is_null($value)
            && !is_array($value)
            && !is_object($value)
            && !is_scalar($value)
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'Argument 1 can be string|int|float|bool|null|array|object. Given "%s"',
                    get_parameter_type($value)
                )
            );
        }

        $this->value = $value;

        return $this;
    }

    public function expiresAt($expiration): static
    {
        if (!is_null($expiration) && !$expiration instanceof DateTimeInterface) {
            throw new InvalidArgumentException(
                sprintf(
                    'Argument 1 must be a type of null|\DateTimeInterface. Given %s',
                    get_parameter_type($expiration)
                )
            );
        }

        $this->expiresAt = expiry_timestamp($expiration);

        return $this;
    }

    public function expiresAfter($time): static
    {
        if (!is_null($time) && !is_int($time) && !$time instanceof DateInterval) {
            throw new InvalidArgumentException(
                sprintf(
                    'Argument 1 must be a type of null|int|\DateInterval. Given %s',
                    get_parameter_type($time)
                )
            );
        }

        $this->expiresAt = expiry_timestamp($time);

        return $this;
    }

    public function getExpiration(): ?int
    {
        return $this->expiresAt;
    }
}
