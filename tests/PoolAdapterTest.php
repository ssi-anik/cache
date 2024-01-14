<?php

use Anik\Cache\Item;
use Anik\Cache\PoolAdapter;
use Anik\Cache\Test\BaseTestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class PoolAdapterTest extends BaseTestCase
{
    protected function getMockedPool()
    {
        return $this->getMockBuilder(CacheItemPoolInterface::class)->getMock();
    }

    protected function getPoolAdapter($pool): PoolAdapter
    {
        return new PoolAdapter($pool);
    }

    public function testGetItemCallsUnderlyingPoolMethod()
    {
        $adapter = $this->getPoolAdapter($mock = $this->getMockedPool());
        $mock->expects($this->once())
             ->method('getItem')
             ->with('key-1')
             ->willReturn($expected = new Item('key-1', 'value', null, true));

        $this->assertSame($expected, $actual = $adapter->getItem('key-1'));
        $this->assertInstanceOf(CacheItemInterface::class, $actual);
    }

    public function testGetItemsCallsUnderlyingPoolMethod()
    {
        $adapter = $this->getPoolAdapter($mock = $this->getMockedPool());
        $mock->expects($this->once())
             ->method('getItems')
             ->with(['key-1'])
             ->willReturn($expected = []);

        $this->assertSame($expected, $actual = $adapter->getItems(['key-1']));
        $this->assertIsArray($actual);
    }

    public function testHasItemCallsUnderlyingPoolMethod()
    {
        $adapter = $this->getPoolAdapter($mock = $this->getMockedPool());
        $mock->expects($this->once())
             ->method('hasItem')
             ->with('key-1')
             ->willReturn(true);

        $this->assertTrue($adapter->hasItem('key-1'));
    }

    public function testClearCallsUnderlyingPoolMethod()
    {
        $adapter = $this->getPoolAdapter($mock = $this->getMockedPool());
        $mock->expects($this->once())
             ->method('clear')
             ->willReturn(false);

        $this->assertFalse($adapter->clear());
    }

    public function testDeleteItemUnderlyingPoolMethod()
    {
        $mock = $this->getMockedPool();

        $mock->expects($this->once())
             ->method('deleteItem')
             ->with('key-1')
             ->willReturn(false);

        $adapter = $this->getPoolAdapter($mock);

        $this->assertFalse($adapter->deleteItem('key-1'));
    }

    public function testSaveCallsUnderlyingPoolMethod()
    {
        $mock = $this->getMockedPool();

        $mock->expects($this->once())
             ->method('save')
             ->with($this->isInstanceOf(CacheItemInterface::class))
             ->willReturn(false);

        $adapter = $this->getPoolAdapter($mock);

        $this->assertFalse($adapter->save(new Item('key-1', 'value-1', null, false)));
    }

    public function testSaveDeferredCallsUnderlyingPoolMethod()
    {
        $mock = $this->getMockedPool();

        $mock->expects($this->once())
             ->method('saveDeferred')
             ->with($this->isInstanceOf(CacheItemInterface::class))
             ->willReturn(false);

        $adapter = $this->getPoolAdapter($mock);

        $this->assertFalse($adapter->saveDeferred(new Item('key-1', 'value-1', null, false)));
    }

    public function testCommitCallsUnderlyingPoolMethod()
    {
        $mock = $this->getMockedPool();

        $mock->expects($this->once())
             ->method('commit')
             ->willReturn(false);

        $adapter = $this->getPoolAdapter($mock);

        $this->assertFalse($adapter->commit());
    }

    public function testGetRetrievesItemUsingGetItem()
    {
        $mock = $this->getMockedPool();

        $mock->expects($this->exactly(3))
             ->method('getItem')
             ->with('key-1')
             ->willReturnOnConsecutiveCalls(
                 new Item('key-1', 'value', null, true),
                 new Item('key-1', null, null, true),
                 new Item('key-1', null, null, false)
             );

        $adapter = $this->getPoolAdapter($mock);

        $this->assertSame('value', $adapter->get('key-1', 'default'));
        $this->assertSame('default', $adapter->get('key-1', 'default'));
        $this->assertSame('default', $adapter->get('key-1', 'default'));
    }

    public function testDeleteRemovesItemUsingDeleteItem()
    {
        $mock = $this->getMockedPool();

        $mock->expects($this->once())
             ->method('deleteItem')
             ->with('key-1')
             ->willReturn(false);

        $adapter = $this->getPoolAdapter($mock);

        $this->assertFalse($adapter->delete('key-1'));
    }

    public function testGetMultipleRetrievesItemUsingGetItems()
    {
        $mock = $this->getMockedPool();

        $mock->expects($this->once())
             ->method('getItems')
             ->with(['key-1', 'key-2', 'key-3'])
             ->willReturn([
                 'key-1' => new Item('key-1', 'value', null, true),
                 'key-2' => new Item('key-2', null, null, true),
                 'key-3' => new Item('key-3', null, null, false),
             ]);

        $adapter = $this->getPoolAdapter($mock);

        $this->assertSame(
            [
                'key-1' => 'value',
                'key-2' => 'default-value',
                'key-3' => 'default-value',
            ],
            $adapter->getMultiple(['key-1', 'key-2', 'key-3'], 'default-value')
        );
    }

    public function testSetMultipleSetsItemUsingSave()
    {
        $mock = $this->getMockedPool();
        $actualParams = [
            'key-1' => 'value',
            'key-2' => null,
        ];

        $mock->expects($this->exactly(2))
             ->method('save')
             ->with($this->callback(function ($item) use ($actualParams) {
                 return is_null($item->getExpiration()) && $actualParams[$item->getkey()] === $item->getValue();
             }))
             ->willReturn(true);

        $adapter = $this->getPoolAdapter($mock);

        $this->assertTrue($adapter->setMultiple($actualParams));
    }

    public function testSetMultipleConsidersTtl()
    {
        $mock = $this->getMockedPool();
        $actualParams = [
            'key-1' => 'value',
            'key-2' => null,
        ];

        $mock->expects($this->exactly(2))
             ->method('save')
             ->with($this->callback(function ($item) use ($actualParams) {
                 return !is_null($item->getExpiration()) && ($actualParams[$item->getkey()] === $item->getValue());
             }))
             ->willReturn(true);

        $adapter = $this->getPoolAdapter($mock);

        $this->assertTrue($adapter->setMultiple($actualParams, 500));
    }

    public function testDeleteMultipleDeletesItemUsingUnderlyingDeleteItems()
    {
        $mock = $this->getMockedPool();

        $mock->expects($this->once())
             ->method('deleteItems')
             ->with(['key-1', 'key-2', 'key-3'])
             ->willReturn(true);

        $adapter = $this->getPoolAdapter($mock);

        $this->assertTrue($adapter->deleteItems(['key-1', 'key-2', 'key-3']));
    }

    public function testHasCallsUnderlyingDeleteItems()
    {
        $mock = $this->getMockedPool();

        $mock->expects($this->once())
             ->method('hasItem')
             ->with('key-1')
             ->willReturn(false);

        $adapter = $this->getPoolAdapter($mock);

        $this->assertFalse($adapter->has('key-1'));
    }
}
