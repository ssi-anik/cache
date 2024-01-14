<?php

namespace Pool;

use Anik\Cache\Exception\InvalidArgumentException;
use Anik\Cache\Item;
use Anik\Cache\Pool\NullPool;
use Anik\Cache\Test\BaseTestCase;

class NullPoolTest extends BaseTestCase
{
    protected function getNullPool(): NullPool
    {
        return new NullPool();
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

        call_user_func([$this->getNullPool(), $method], $key);
    }

    public function testItemIsNeverStoredUsingSaveMethod()
    {
        $item = new Item('key-1', 'value-1', null, false);
        $this->getNullPool()->save($item);

        $this->assertFalse($this->getNullPool()->getItem('key-1')->isHit());
        $this->assertFalse($this->getNullPool()->hasItem('key-1'));
    }

    public function testItemIsNeverStoredUsingSaveDeferredAndCommitMethod()
    {
        $item = new Item('key-2', 'value-1', null, false);
        $pool = $this->getNullPool();
        $pool->saveDeferred($item);
        $pool->commit();

        $this->assertFalse($this->getNullPool()->getItem('key-2')->isHit());
        $this->assertFalse($this->getNullPool()->hasItem('key-2'));
    }

    public function testGetItemMethodAlwaysReturnsItemWithIsHitFalse()
    {
        $pool = new NullPool();

        $pool->save(new Item('key-1', 'value', null, false));
        $this->assertFalse($pool->getItem('key-1')->isHit());

        $pool->save(new Item('key-2', 'value', null, false));
        $this->assertFalse($pool->getItem('key-2')->isHit());
    }

    public function testGetItemsMethodAlwaysReturnsArrayOfItemsWithIsHitFalse()
    {
        $pool = new NullPool();

        $pool->save(new Item('key-1', 'value', null, false));
        $pool->save(new Item('key-2', 'value', null, false));

        $count = array_filter(array_map(function ($item) {
            return $item->isHit();
        }, $pool->getItems(['key-1', 'key-2'])));

        $this->assertCount(0, $count);
    }

    public function testHasItemMethodAlwaysReturnsFalse()
    {
        $pool = new NullPool();

        $pool->save(new Item('key-1', 'value', null, false));
        $this->assertFalse($pool->hasItem('key-1'));

        $pool->save(new Item('key-2', 'value', null, false));
        $this->assertFalse($pool->hasItem('key-2'));
    }

    public function testClearMethodReturnsTrue()
    {
        $pool = new NullPool();
        $this->assertTrue($pool->clear());
    }

    public function testDeleteItemMethodReturnsTrue()
    {
        $pool = new NullPool();
        $this->assertTrue($pool->deleteItem('key'));
    }

    public function objectConstructionDataProvider(): array
    {
        return [
            'does not pass value - uses default' => [null, false],
            'sets false value' => [false, false],
            'sets true value' => [true, true],
        ];
    }

    /** @dataProvider objectConstructionDataProvider */
    public function testSaveMethodsReturnDefaultValue($param, $expected)
    {
        $pool = is_null($param) ? new NullPool() : new NullPool($param);

        $item = new Item('key', 'value', null, false);
        $this->assertEquals($expected, $pool->save($item));
        $this->assertEquals($expected, $pool->saveDeferred($item));
        $this->assertEquals($expected, $pool->commit());
    }
}
