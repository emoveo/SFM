<?php

use SFM\Value\Value;
use SFM\Value\ValueStorage;

class ValueTest extends PHPUnit_Framework_TestCase
{
    protected function getStorageStrategy()
    {
        $storageStrategy = $this->getMockBuilder('SFM\Value\ValueStorageStrategyInterface')
            ->disableOriginalConstructor()
            ->setMethods(['deleteRaw', 'getRaw', 'setRaw'])
            ->getMock();

        return $storageStrategy;
    }

    public function testValueFetchNotExisting()
    {
        $function = $this->getMockBuilder('stdObject')
            ->setMethods(['__invoke'])
            ->getMock();

        $function->expects($this->once())
            ->method('__invoke')
            ->willReturn('test');

        $value = new Value($function, 'key', 1);

        $storageStrategy = $this->getStorageStrategy();

        $storageStrategy->expects($this->once())
            ->method('getRaw')
            ->willReturn('key')
            ->willReturn(null);

        $valueStorage = new ValueStorage($storageStrategy);
        $data = $valueStorage->get($value);

        $this->assertEquals('test', $data);
    }

    public function testValueFetchExisting()
    {
        $function = $this->getMockBuilder('stdObject')
            ->setMethods(['__invoke'])
            ->getMock();

        $function->expects($this->never())
            ->method('__invoke');

        $value = new Value($function, 'key', 1);

        $storageStrategy = $this->getStorageStrategy();

        $storageStrategy->expects($this->once())
            ->method('getRaw')
            ->with('key')
            ->willReturn('test');

        $valueStorage = new ValueStorage($storageStrategy);
        $data = $valueStorage->get($value);

        $this->assertEquals('test', $data);
    }

    public function testNotCallable()
    {
        $this->setExpectedException('SFM\BaseException', 'Argument `function` must be callable');
        new Value('not_callable', 'key', 1);
    }
} 