<?php

use SFM\Cache\Driver\MemcachedDriver;

class MemcachedDriverTest extends PHPUnit_Framework_TestCase
{
    protected function getMockedMemcache()
    {
        $memcached = $this->getMockBuilder('stdObject')
            ->disableOriginalConstructor()
            ->setMethods(['addServer', 'flush', 'get', 'getMulti', 'setMulti', 'set', 'delete'])
            ->getMock();

        return $memcached;
    }

    public function testAddServer()
    {
        $memcached = $this->getMockedMemcache();

        $memcached->expects($this->once())
            ->method('addServer')
            ->with("host", 20, 1)
            ->willReturn(true);

        $driver = new MemcachedDriver($memcached);
        $this->assertEquals(true, $driver->addServer("host", 20, 1));
    }

    public function testAddServer2()
    {
        $memcached = $this->getMockedMemcache();

        $memcached->expects($this->once())
            ->method('addServer')
            ->with("host", 20, 0)
            ->willReturn(true);

        $driver = new MemcachedDriver($memcached);
        $this->assertEquals(true, $driver->addServer("host", 20));
    }

    public function testFlush()
    {
        $memcached = $this->getMockedMemcache();

        $memcached->expects($this->once())
            ->method('flush')
            ->willReturn(true);

        $driver = new MemcachedDriver($memcached);
        $this->assertEquals(true, $driver->flush());
    }

    public function testGetFailure()
    {
        $memcached = $this->getMockedMemcache();

        $memcached->expects($this->once())
            ->method('get')
            ->with('test')
            ->willReturn(false);

        $driver = new MemcachedDriver($memcached);
        $this->assertEquals(null, $driver->get('test'));
    }

    public function testGetSuccess()
    {
        $memcached = $this->getMockedMemcache();

        $memcached->expects($this->once())
            ->method('get')
            ->with('test')
            ->willReturn('value');

        $driver = new MemcachedDriver($memcached);
        $this->assertEquals('value', $driver->get('test'));
    }

    public function testGetMultiFailure()
    {
        $memcached = $this->getMockedMemcache();

        $memcached->expects($this->once())
            ->method('getMulti')
            ->with(['test1', 'test2'])
            ->willReturn(false);

        $driver = new MemcachedDriver($memcached);
        $this->assertEquals([], $driver->getMulti(['test1', 'test2']));
    }

    public function testGetMultiSuccess()
    {
        $memcached = $this->getMockedMemcache();

        $memcached->expects($this->once())
            ->method('getMulti')
            ->with(['test1', 'test2'])
            ->willReturn(['test1' => 'value1']);

        $driver = new MemcachedDriver($memcached);
        $this->assertEquals(['test1' => 'value1'], $driver->getMulti(['test1', 'test2']));
    }

    public function testSetMulti()
    {
        $memcached = $this->getMockedMemcache();

        $memcached->expects($this->once())
            ->method('setMulti')
            ->with(['test' => 'value'], 1)
            ->willReturn(true);

        $driver = new MemcachedDriver($memcached);
        $this->assertEquals(true, $driver->setMulti(['test' => 'value'], 1));
    }

    public function testSet()
    {
        $memcached = $this->getMockedMemcache();

        $memcached->expects($this->once())
            ->method('set')
            ->with('test', 'value', 1)
            ->willReturn(true);

        $driver = new MemcachedDriver($memcached);
        $this->assertEquals(true, $driver->set('test', 'value', 1));
    }

    public function testDelete()
    {
        $memcached = $this->getMockedMemcache();

        $memcached->expects($this->once())
            ->method('delete')
            ->with('test')
            ->willReturn(true);

        $driver = new MemcachedDriver($memcached);

        $this->assertEquals(true, $driver->delete('test'));
    }
} 