<?php

namespace Pool;

use Anik\Cache\Exception\InvalidArgumentException;
use Anik\Cache\Item;
use Anik\Cache\Pool\InMemoryPool;
use Anik\Cache\Test\BaseTestCase;
use DateInterval;
use Psr\Cache\CacheItemInterface;

class InMemoryPoolTest extends BaseTestCase
{
    protected function getInMemoryPool(): InMemoryPool
    {
        return new InMemoryPool();
    }

    public function reservedCharacterCheckForKeyDataProvider(): array
    {
        return [
            'valid key for getItem' => ['getItem', 'key', false],
            'reserved char for getItem' => ['getItem', 'key@', true],
            'empty string as key for getItem' => ['getItem', '', true],
            'valid key for hasItem' => ['hasItem', 'key', false],
            'reserved char for hasItem' => ['hasItem', 'key@', true],
            'empty string as key for hasItem' => ['hasItem', '', true],
            'valid key for deleteItem' => ['deleteItem', 'key', false],
            'reserved char for deleteItem' => ['deleteItem', 'key@', true],
            'empty string as key for deleteItem' => ['deleteItem', '', true],
            'valid key for getItems' => ['getItems', ['key'], false],
            'reserved char for getItems' => ['getItems', ['key@'], true],
            'empty string as key for getItems' => ['getItems', [''], true],
            'valid key for deleteItems' => ['deleteItems', ['key'], false],
            'reserved char for deleteItems' => ['deleteItems', ['key@'], true],
            'empty string as key for deleteItems' => ['deleteItems', [''], true],
        ];
    }

    /** @dataProvider reservedCharacterCheckForKeyDataProvider */
    public function testKeyRelatedMethodsValidateKeyForReservedCharacter($method, $key, $expectsException)
    {
        if ($expectsException) {
            $this->expectException(InvalidArgumentException::class);
        } else {
            $this->addToAssertionCount(1);
        }

        call_user_func([$this->getInMemoryPool(), $method], $key);
    }

    public function testItemIsStoredUsingSaveMethod()
    {
        $dateInterval = DateInterval::createFromDateString('+100 seconds');
        $item = new Item('key-1', 'value-1', $dateInterval, false);
        $pool = $this->getInMemoryPool();
        $pool->save($item);

        $this->assertTrue(($retrievedItem = $pool->getItem('key-1'))->isHit());
        $this->assertTrue($pool->hasItem('key-1'));
        $this->assertEquals('value-1', $retrievedItem->getValue());
    }

    public function testItemIsStoredUsingSaveDeferredAndCommitMethod()
    {
        $item = new Item('key-2', 'value-2', null, false);
        $pool = $this->getInMemoryPool();
        $pool->saveDeferred($item);
        $pool->commit();

        $this->assertTrue($pool->getItem('key-2')->isHit());
        $this->assertTrue($pool->hasItem('key-2'));
    }

    public function testItemIsNotStoredUsingSaveDeferredIfCommitMethodIsNotCalled()
    {
        $item = new Item('key-3', 'value-3', null, false);
        $pool = $this->getInMemoryPool();
        $pool->saveDeferred($item);

        $this->assertFalse($pool->getItem('key-3')->isHit());
        $this->assertFalse($pool->hasItem('key-3'));
    }

    public function testGetItemMethodAlwaysReturnsItemWithAppropriateIsHitValue()
    {
        $pool = $this->getInMemoryPool();

        $pool->save(new Item('key-1', 'value-1', null, false));
        $this->assertTrue($pool->getItem('key-1')->isHit());

        $pool->save(new Item('key-2', 'value-2', null, false));
        $this->assertTrue($pool->getItem('key-2')->isHit());

        $this->assertFalse($pool->getItem('key-3')->isHit());
    }

    public function testGetItemsMethodAlwaysReturnsArrayOfItemsWithAppropriateIsHitValue()
    {
        $pool = $this->getInMemoryPool();

        $pool->save(new Item('key-1', 'value', null, false));
        $pool->save(new Item('key-2', 'value', null, false));

        $count = array_filter(array_map(function ($item) {
            return $item->isHit();
        }, $pool->getItems(['key-1', 'key-2'])));

        $this->assertCount(2, $count);
    }

    public function testHasItemMethodReturnsAppropriateValueOnHit()
    {
        $pool = $this->getInMemoryPool();

        $pool->save(new Item('key-1', 'value', null, false));
        $this->assertTrue($pool->hasItem('key-1'));

        $this->assertFalse($pool->hasItem('key-2'));
    }

    public function testClearMethodReturnsTrueAndClearsItems()
    {
        $pool = $this->getInMemoryPool();

        $pool->save(new Item('key-1', 'value', null, false));
        $this->assertTrue($pool->hasItem('key-1'));

        $this->assertTrue($pool->clear());

        $this->assertFalse($pool->hasItem('key-1'));
    }

    public function testDeleteItemMethodReturnsTrue()
    {
        $pool = $this->getInMemoryPool();

        $pool->save(new Item('key-1', 'value', null, false));
        $this->assertTrue($pool->hasItem('key-1'));

        $this->assertTrue($pool->deleteItem('key-1'));

        $this->assertFalse($pool->hasItem('key-1'));
    }

    public function testGetItemMethodRemovesExpiredItem()
    {
        $pool = $this->getInMemoryPool();

        $expiry = DateInterval::createFromDateString('-100 seconds');
        $pool->save(new Item('key-1', 'value', $expiry, false));
        $this->assertFalse($pool->getItem('key-1')->isHit());
    }

    public function testHasItemMethodReturnsFalseWhenItemExpires()
    {
        $pool = $this->getInMemoryPool();

        $expiry = DateInterval::createFromDateString('-100 seconds');
        $pool->save(new Item('key-1', 'value', $expiry, false));
        $this->assertFalse($pool->hasItem('key-1'));
    }

    public function testStoreItemOnlyAcceptExtendedCacheItemInterface()
    {
        $item = $this->getMockBuilder(CacheItemInterface::class)->getMock();
        $pool = $this->getInMemoryPool();

        $this->assertTrue($pool->save(new Item('key-1', 'value', null, false)));
        $this->assertFalse($pool->save($item));
    }
}
