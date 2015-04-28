<?php

use SFM\Cache\Adapter;

class AdapterTest extends PHPUnit_Framework_TestCase
{
    protected function getMockedAdapter()
    {
        $driver = $this->getMockBuilder('SFM\Cache\Driver\DriverInterface')
            ->disableOriginalConstructor()
            ->setMethods(['addServer', 'flush', 'set', 'setMulti', 'delete', 'get', 'getMulti'])
            ->getMock();

        return $driver;
    }

    public function testNotTransactionAddServer()
    {
        $driver = $this->getMockedAdapter();

        $driver->expects($this->once())
               ->method('addServer')
               ->with('host', 20, 1);

        $adapter = new Adapter($driver);
        $adapter->addServer('host', 20, 1);
    }

    public function testTransactionAddServer()
    {
        $driver = $this->getMockedAdapter();

        $driver->expects($this->never())
               ->method('addServer');

        $this->setExpectedException('SFM\Transaction\TransactionException', "Can't `addServer` while in transaction");

        $adapter = new Adapter($driver);
        $adapter->beginTransaction();
        $adapter->addServer('host', 20, 1);
    }

    public function testNotTransactionFlush()
    {
        $driver = $this->getMockedAdapter();

        $driver->expects($this->once())
            ->method('flush');

        $adapter = new Adapter($driver);
        $adapter->flush();
    }

    public function testTransactionFlush()
    {
        $driver = $this->getMockedAdapter();

        $driver->expects($this->never())
            ->method('flush');

        $this->setExpectedException('SFM\Transaction\TransactionException', "Can't `flush` while in transaction");

        $adapter = new Adapter($driver);
        $adapter->beginTransaction();
        $adapter->flush();
    }

    public function testTransactionActiveSet()
    {
        $driver = $this->getMockedAdapter();

        $driver->expects($this->never())
               ->method('set');

        $adapter = new Adapter($driver);

        $this->assertEquals(null, $adapter->get('test'));

        $adapter->beginTransaction();
        $adapter->set('test', 'value1');
        $this->assertEquals('value1', $adapter->get('test'));
    }

    public function testTransactionActiveSetCommit()
    {
        $driver = $this->getMockedAdapter();

        $driver->expects($this->once())
               ->method('get')
               ->with($this->equalTo('test'))
               ->willReturn(null);

        $driver->expects($this->once())
               ->method('set')
               ->willReturn('test', 'value1');

        $adapter = new Adapter($driver);
        $this->assertEquals(null, $adapter->get('test'));

        $adapter->beginTransaction();
        $adapter->set('test', 'value1');
        $this->assertEquals('value1', $adapter->get('test'));
        $adapter->commitTransaction();
    }

    public function testTransactionActiveSetRollback()
    {
        $driver = $this->getMockedAdapter();

        $driver->expects($this->once())
            ->method('get')
            ->with($this->equalTo('test'))
            ->willReturn(null);

        $driver->expects($this->never())
               ->method('set');

        $driver->expects($this->never())
               ->method('setMulti');

        $adapter = new Adapter($driver);
        $this->assertEquals(null, $adapter->get('test'));

        $adapter->beginTransaction();
        $adapter->set('test', 'value1');
        $this->assertEquals('value1', $adapter->get('test'));
        $adapter->rollbackTransaction();
    }

    public function testTransactionDeleteSet()
    {
        $driver = $this->getMockedAdapter();

        $driver->expects($this->once())
               ->method("set")
               ->with("test", "value1");

        $driver->expects($this->never())
               ->method("delete");

        $adapter = new Adapter($driver);
        $adapter->beginTransaction();
        $adapter->delete("test");
        $adapter->set("test", "value1");
        $adapter->commitTransaction();
    }

    public function testTransactionSetDelete()
    {
        $driver = $this->getMockedAdapter();

        $driver->expects($this->never())
               ->method("set");

        $driver->expects($this->once())
               ->method("delete")
               ->with("test");

        $adapter = new Adapter($driver);
        $adapter->beginTransaction();
        $adapter->set("test", "value1");
        $adapter->delete("test");
        $adapter->commitTransaction();
    }

    public function testTransactionDeleteSetRollback()
    {
        $driver = $this->getMockedAdapter();

        $driver->expects($this->never())
               ->method("set");

        $driver->expects($this->never())
               ->method("delete");

        $adapter = new Adapter($driver);
        $adapter->beginTransaction();
        $adapter->delete("test");
        $adapter->set("test", "value1");
        $adapter->rollbackTransaction();
    }

    public function testNotTransactionSetMulti()
    {
        $driver = $this->getMockedAdapter();

        $driver->expects($this->once())
               ->method('setMulti')
               ->with(["test1" => "value1", "test2" => "value2"]);

        $adapter = new Adapter($driver);
        $adapter->setMulti(["test1" => "value1", "test2" => "value2"]);
    }

    public function testTransactionSetMulti()
    {
        $driver = $this->getMockedAdapter();

        $driver->expects($this->never())
            ->method('setMulti');

        $adapter = new Adapter($driver);
        $adapter->beginTransaction();
        $adapter->setMulti(["test1" => "value1", "test2" => "value2"]);
    }

    public function testTransactionSetMultiCommit()
    {
        $driver = $this->getMockedAdapter();

        $driver->expects($this->at(0))
               ->method('set')
               ->with("test1", "value1");

        $driver->expects($this->at(1))
               ->method('set')
               ->with("test2", "value2");

        $adapter = new Adapter($driver);
        $adapter->beginTransaction();
        $adapter->setMulti(["test1" => "value1", "test2" => "value2"]);
        $adapter->commitTransaction();
    }

    public function testTransactionDeleteSetMultiCommit()
    {
        $driver = $this->getMockedAdapter();

        $driver->expects($this->at(0))
               ->method('set')
               ->with("test1", "value1");

        $driver->expects($this->at(1))
               ->method('set')
               ->with("test2", "value2");

        $driver->expects($this->never())
               ->method('delete');

        $adapter = new Adapter($driver);
        $adapter->beginTransaction();
        $adapter->delete("test1");
        $adapter->setMulti(["test1" => "value1", "test2" => "value2"]);
        $adapter->commitTransaction();
    }

    public function testTransactionSetMultiDeleteCommit()
    {
        $driver = $this->getMockedAdapter();

        $driver->expects($this->at(0))
               ->method('set')
               ->with("test2", "value2");

        $driver->expects($this->at(1))
               ->method('delete')
               ->with('test1');

        $adapter = new Adapter($driver);
        $adapter->beginTransaction();
        $adapter->setMulti(["test1" => "value1", "test2" => "value2"]);
        $adapter->delete("test1");
        $adapter->commitTransaction();
    }

    public function testTransactionSetMultiDeleteRollback()
    {
        $driver = $this->getMockedAdapter();

        $driver->expects($this->never())
               ->method('set');

        $driver->expects($this->never())
               ->method('delete');

        $adapter = new Adapter($driver);
        $adapter->beginTransaction();
        $adapter->setMulti(["test1" => "value1", "test2" => "value2"]);
        $adapter->delete("test1");
        $adapter->rollbackTransaction();
    }

    public function testNotTransactionDelete()
    {
        $driver = $this->getMockedAdapter();

        $driver->expects($this->once())
               ->method('delete')
               ->with('test1');

        $adapter = new Adapter($driver);
        $adapter->delete("test1");
    }

    public function testTransactionDelete()
    {
        $driver = $this->getMockedAdapter();

        $driver->expects($this->never())
               ->method('delete');

        $adapter = new Adapter($driver);
        $adapter->beginTransaction();
        $adapter->delete("test1");
    }

    public function testTransactionDeleteCommit()
    {
        $driver = $this->getMockedAdapter();

        $driver->expects($this->once())
               ->method('delete')
               ->with("test1");

        $adapter = new Adapter($driver);
        $adapter->beginTransaction();
        $adapter->delete("test1");
        $adapter->commitTransaction();
    }

    public function testNotTransactionGet()
    {
        $driver = $this->getMockedAdapter();

        $driver->expects($this->once())
               ->method('get')
               ->with('test');

        $adapter = new Adapter($driver);
        $adapter->get('test');
    }

    public function testTransactionSetGetCommit()
    {
        $driver = $this->getMockedAdapter();

        $driver->expects($this->at(0))
               ->method('set')
               ->with('test', 'value1');

        $driver->expects($this->at(1))
               ->method('set')
               ->with('test', 'value2');

        $adapter = new Adapter($driver);
        $adapter->set('test', 'value1');
        $adapter->beginTransaction();
        $adapter->set('test', 'value2');
        $adapter->commitTransaction();
    }

    public function testTransactionSetGetRollback()
    {
        $driver = $this->getMockedAdapter();

        $driver->expects($this->at(0))
            ->method('set')
            ->with('test', 'value1');

        $adapter = new Adapter($driver);
        $adapter->set('test', 'value1');
        $adapter->beginTransaction();
        $adapter->set('test', 'value2');
        $adapter->rollbackTransaction();
    }

    public function testTransactionDeleteGet()
    {
        $driver = $this->getMockedAdapter();

        $driver->expects($this->never())
               ->method('get')
               ->with('test');

        $adapter = new Adapter($driver);
        $adapter->beginTransaction();
        $adapter->delete('test');
        $adapter->get('test');
    }

    public function testTransactionGet()
    {
        $driver = $this->getMockedAdapter();

        $driver->expects($this->once())
               ->method('get')
               ->with('test');

        $adapter = new Adapter($driver);
        $adapter->beginTransaction();
        $adapter->get('test');
    }

    public function testNotTransactionGetMulti()
    {
        $driver = $this->getMockedAdapter();

        $driver->expects($this->once())
               ->method('getMulti')
               ->with(['test1', 'test2'])
               ->willReturn([]);

        $adapter = new Adapter($driver);
        $adapter->getMulti(['test1', 'test2']);
    }

    public function testTransactionGetMulti()
    {
        $driver = $this->getMockedAdapter();

        $driver->expects($this->once())
               ->method('getMulti')
               ->with(['test1', 'test2'])
               ->willReturn(['test1' => 'value1', 'test2' => 'value2']);

        $adapter = new Adapter($driver);
        $adapter->beginTransaction();
        $adapter->set('test1', 'value3');
        $data = $adapter->getMulti(['test1', 'test2']);
        $this->assertEquals(['test1' => 'value3', 'test2' => 'value2'], $data);
    }

    public function testTransactionGetMultiWithDelete()
    {
        $driver = $this->getMockedAdapter();

        $driver->expects($this->once())
            ->method('getMulti')
            ->with(['test1'])
            ->willReturn(['test1' => 'value1']);

        $adapter = new Adapter($driver);
        $adapter->beginTransaction();
        $adapter->set('test1', 'value3');
        $adapter->delete('test2');
        $data = $adapter->getMulti(['test1', 'test2']);
        $this->assertEquals(['test1' => 'value3'], $data);
    }

    public function testTransactionGetMultiRollback()
    {
        $driver = $this->getMockedAdapter();

        $driver->expects($this->once())
            ->method('getMulti')
            ->with(['test1', 'test2'])
            ->willReturn(['test1' => 'value1', 'test2' => 'value2']);

        $adapter = new Adapter($driver);
        $adapter->beginTransaction();
        $adapter->set('test1', 'value3');
        $adapter->rollbackTransaction();
        $data = $adapter->getMulti(['test1', 'test2']);
        $this->assertEquals(['test1' => 'value1', 'test2' => 'value2'], $data);
    }

}