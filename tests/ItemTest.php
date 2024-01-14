<?php

namespace Anik\Cache\Test;

use Anik\Cache\Exception\InvalidArgumentException;
use Anik\Cache\Item;
use DateInterval;
use DateTimeImmutable;

class ItemTest extends BaseTestCase
{
    public function itemsInitializationDataProvider(): array
    {
        return [
            'only key is set' => [
                [
                    'params' => [
                        'key',
                    ],
                    'expectations' => [
                        'getKey' => 'key',
                        'getValue' => null,
                        'getExpiration' => null,
                        'isHit' => false,
                    ],
                ],
            ],
            'key & value is set' => [
                [
                    'params' => [
                        'key',
                        'value',
                    ],
                    'expectations' => [
                        'getKey' => 'key',
                        'getValue' => 'value',
                        'getExpiration' => null,
                        'isHit' => false,
                    ],
                ],
            ],
            'key, value, expiresAt is set' => [
                [
                    'params' => [
                        'key',
                        'value',
                        60,
                    ],
                    'expectations' => [
                        'getKey' => 'key',
                        'getValue' => 'value',
                        'getExpiration' => (new DateTimeImmutable())->modify('+60 seconds')->getTimestamp(),
                        'isHit' => false,
                    ],
                ],
            ],
            'all values are set' => [
                [
                    'params' => [
                        'key',
                        'value',
                        60,
                        true,
                    ],
                    'expectations' => [
                        'getKey' => 'key',
                        'getValue' => 'value',
                        'getExpiration' => (new DateTimeImmutable())->modify('+60 seconds')->getTimestamp(),
                        'isHit' => true,
                    ],
                ],
            ],
        ];
    }

    /** @dataProvider itemsInitializationDataProvider */
    public function testItemIsInitializedProperly(array $data)
    {
        $item = new Item(...$data['params']);
        foreach ($data['expectations'] as $method => $expectation) {
            $actual = call_user_func([$item, $method]);
            if ($method !== 'getExpiration') {
                $this->assertSame($expectation, $actual);
            } else {
                $this->assertEqualsWithDelta($expectation, $actual, 1);
            }
        }
    }

    public function testGetMethodOnItemReturnsNullWhenIsHitIsFalse()
    {
        $item = new Item('key', 'value', null, false);
        $this->assertNull($item->get());

        $item = new Item('key2', 'new-value', null, true);
        $this->assertSame('new-value', $item->get());
    }

    public function testExpirationCanBeSetOnItemByCallingExpireMethods()
    {
        $item = new Item('key', 'value', null, false);
        $this->assertNull($item->getExpiration());

        $newTime = ($now = new DateTimeImmutable())->modify('+100 seconds');

        $item->expiresAt($newTime);
        $this->assertSame($newTime->getTimestamp(), $item->getExpiration());

        $item->expiresAfter(200);
        $this->assertSame($now->modify('200 seconds')->getTimestamp(), $item->getExpiration());
    }

    public function testGetMethodOnItemReturnsNullIfIsHitIsFalse()
    {
        $item = new Item('key', 'value', null, false);

        $this->assertNull($item->get());
    }

    public function testValueCanBeSetOnItemByCallingTheSetMethod()
    {
        $item = new Item('key', 'value', null, false);
        $item->set('new value');
        $this->assertSame('new value', $item->getValue());
    }

    public function testSetMethodCanOnlyReceiveAllowedValues()
    {
        $file = fopen('phpunit.xml', 'r');
        $item = new Item('key', null, null, false);
        $this->expectException(InvalidArgumentException::class);
        $item->set($file);
    }

    public function expireMethodsDataProvider(): array
    {
        return [
            'expiresAt method with null' => [
                'method' => 'expiresAt',
                'value' => null,
                'exception' => false,
            ],
            'expiresAt method with DateTimeInterface' => [
                'method' => 'expiresAt',
                'value' => new DateTimeImmutable(),
                'exception' => false,
            ],
            'expiresAt method with value to raise exception' => [
                'method' => 'expiresAt',
                'value' => 'test',
                'exception' => true,
            ],
            'expiresAfter method with null' => [
                'method' => 'expiresAfter',
                'value' => null,
                'exception' => false,
            ],
            'expiresAfter method with int' => [
                'method' => 'expiresAfter',
                'value' => 1000,
                'exception' => false,
            ],
            'expiresAfter method with DateInterval' => [
                'method' => 'expiresAfter',
                'value' => DateInterval::createFromDateString('100 seconds'),
                'exception' => false,
            ],
            'expiresAfter method with value to raise exception' => [
                'method' => 'expiresAfter',
                'value' => 'test',
                'exception' => true,
            ],
        ];
    }

    /** @dataProvider expireMethodsDataProvider */
    public function testExpireMethodCanOnlyReceiveAllowedValues($method, $value, $exception)
    {
        if ($exception) {
            $this->expectException(InvalidArgumentException::class);
        } else {
            $this->addToAssertionCount(1);
        }

        $item = new Item('key', null, null, false);
        $item->{$method}($value);
    }
}
