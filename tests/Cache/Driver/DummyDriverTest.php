<?php

use SFM\Cache\Driver\DummyDriver;

/**
 * Class DummyDriverTest
 */
class DummyDriverTest extends PHPUnit_Framework_TestCase
{
    public function testAddServer()
    {
        $driver = new DummyDriver();
        $this->assertEquals(true, $driver->addServer("host", 20, 1));
    }

    public function testFlush()
    {
        $driver = new DummyDriver();
        $this->assertEquals(true, $driver->flush());
    }

    public function testGet()
    {
        $driver = new DummyDriver();
        $this->assertEquals(null, $driver->get('test'));
    }

    public function testGetMulti()
    {
        $driver = new DummyDriver();
        $this->assertEquals([], $driver->getMulti([]));
    }

    public function testSet()
    {
        $driver = new DummyDriver();
        $this->assertEquals(true, $driver->set('test', 'value', 1));
    }

    public function testSetMulti()
    {
        $driver = new DummyDriver();
        $this->assertEquals(true, $driver->setMulti(['test' => 'value'], 1));
    }

    public function testDelete()
    {
        $driver = new DummyDriver();
        $this->assertEquals(true, $driver->delete('test'));
    }
}