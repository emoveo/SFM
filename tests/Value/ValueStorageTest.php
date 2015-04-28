<?php

use SFM\Value\ValueStorage;

class ValueStorageTest extends PHPUnit_Framework_TestCase
{
    public function testValueFlush()
    {
        $value = $this->getMockBuilder('SFM\Value\ValueInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getKey', 'getValue', 'getExpiration'])
            ->getMock();

        $value->expects($this->once())
            ->method('getKey')
            ->willReturn('test');

        $storageStrategy = $this->getMockBuilder('SFM\Value\ValueStorageStrategyInterface')
            ->disableOriginalConstructor()
            ->setMethods(['deleteRaw', 'getRaw', 'setRaw'])
            ->getMock();

        $storageStrategy->expects($this->once())
            ->method('deleteRaw')
            ->with('test');

        $storage = new ValueStorage($storageStrategy);
        $storage->flush($value);
    }

    public function testValueGetNotExisting()
    {
        $value = $this->getMockBuilder('SFM\Value\ValueInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getKey', 'getValue', 'getExpiration'])
            ->getMock();

        $value->expects($this->any())
            ->method('getKey')
            ->willReturn('test');

        $value->expects($this->once())
            ->method('getValue')
            ->willReturn('value');

        $value->expects($this->once())
            ->method('getExpiration')
            ->willReturn(1);

        $storageStrategy = $this->getMockBuilder('SFM\Value\ValueStorageStrategyInterface')
            ->disableOriginalConstructor()
            ->setMethods(['deleteRaw', 'getRaw', 'setRaw'])
            ->getMock();

        $storageStrategy->expects($this->once())
            ->method('getRaw')
            ->with('test')
            ->willReturn(null);

        $storageStrategy->expects($this->once())
            ->method('setRaw')
            ->with('test', 'value', 1);

        $storage = new ValueStorage($storageStrategy);
        $data = $storage->get($value);

        $this->assertEquals('value', $data);
    }

    public function testValueGetExisting()
    {
        $value = $this->getMockBuilder('SFM\Value\ValueInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getKey', 'getValue', 'getExpiration'])
            ->getMock();

        $value->expects($this->any())
            ->method('getKey')
            ->willReturn('test');

        $value->expects($this->never())
            ->method('getValue');

        $value->expects($this->never())
            ->method('getExpiration');

        $storageStrategy = $this->getMockBuilder('SFM\Value\ValueStorageStrategyInterface')
            ->disableOriginalConstructor()
            ->setMethods(['deleteRaw', 'getRaw', 'setRaw'])
            ->getMock();

        $storageStrategy->expects($this->once())
            ->method('getRaw')
            ->with('test')
            ->willReturn('value');

        $storageStrategy->expects($this->never())
            ->method('setRaw');

        $storage = new ValueStorage($storageStrategy);
        $data = $storage->get($value);

        $this->assertEquals('value', $data);
    }
} 