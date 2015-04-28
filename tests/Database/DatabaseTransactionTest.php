<?php

use SFM\Database\DatabaseProvider;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Adapter\Driver\DriverInterface;

class DatabaseTransactionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockedAdapter()
    {
        $adapter = $this->getMockBuilder('Zend\Db\Adapter\Adapter')
            ->disableOriginalConstructor()
            ->setMethods(['getDriver'])
            ->getMock();

        return $adapter;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockedDriver()
    {
        $driver = $this->getMockBuilder('Zend\Db\Adapter\Driver\DriverInterface')
            ->disableOriginalConstructor()
            ->setMethods([
                    'beginTransaction', 'getDatabasePlatformName', 'checkEnvironment', 'getConnection', 'createStatement', 'createResult',
                    'getPrepareType', 'formatParameterName', 'getLastGeneratedValue'
                ])
            ->getMock();

        return $driver;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockedConnection()
    {
        $connection = $this->getMockBuilder('Zend\Db\Adapter\Driver\ConnectionInterface')
            ->disableOriginalConstructor()
            ->getMock();

        return $connection;
    }

    public function testBegin()
    {
        $adapter = $this->getMockedAdapter();
        $driver = $this->getMockedDriver();
        $connection = $this->getMockedConnection();

        $adapter->expects($this->any())
            ->method('getDriver')
            ->willReturn($driver);

        $driver->expects($this->any())
            ->method('getConnection')
            ->willReturn($connection);

        $connection->expects($this->once())
            ->method('beginTransaction');

        $t = new DatabaseProvider($adapter);
        $t->beginTransaction();
    }

    public function testMultipleBegin()
    {
        $adapter = $this->getMockedAdapter();
        $driver = $this->getMockedDriver();
        $connection = $this->getMockedConnection();

        $adapter->expects($this->any())
            ->method('getDriver')
            ->willReturn($driver);

        $driver->expects($this->any())
            ->method('getConnection')
            ->willReturn($connection);

        $connection->expects($this->once())
            ->method('beginTransaction');

        $this->setExpectedException('SFM\Transaction\TransactionException', "Can't begin transaction while another one is running");

        $t = new DatabaseProvider($adapter);
        $t->beginTransaction();
        $t->beginTransaction();
    }

    public function testCommit()
    {
        $adapter = $this->getMockedAdapter();
        $driver = $this->getMockedDriver();
        $connection = $this->getMockedConnection();

        $adapter->expects($this->any())
            ->method('getDriver')
            ->willReturn($driver);

        $driver->expects($this->any())
            ->method('getConnection')
            ->willReturn($connection);

        $connection->expects($this->at(0))
            ->method('beginTransaction');

        $connection->expects($this->at(1))
            ->method('commit');

        $t = new DatabaseProvider($adapter);
        $t->beginTransaction();
        $t->commitTransaction();
    }

    public function testCommitWithoutBegin()
    {
        $adapter = $this->getMockedAdapter();
        $driver = $this->getMockedDriver();
        $connection = $this->getMockedConnection();

        $adapter->expects($this->any())
            ->method('getDriver')
            ->willReturn($driver);

        $driver->expects($this->any())
            ->method('getConnection')
            ->willReturn($connection);

        $connection->expects($this->never())
            ->method('commit');

        $this->setExpectedException('SFM\Transaction\TransactionException', "Can't commit transaction while no one is running");

        $t = new DatabaseProvider($adapter);
        $t->commitTransaction();
    }

    public function testRollback()
    {
        $adapter = $this->getMockedAdapter();
        $driver = $this->getMockedDriver();
        $connection = $this->getMockedConnection();

        $adapter->expects($this->any())
            ->method('getDriver')
            ->willReturn($driver);

        $driver->expects($this->any())
            ->method('getConnection')
            ->willReturn($connection);

        $connection->expects($this->at(0))
            ->method('beginTransaction');

        $connection->expects($this->at(1))
            ->method('rollbackTransaction');

        $t = new DatabaseProvider($adapter);
        $t->beginTransaction();
        $t->rollbackTransaction();
    }

    public function testRollbackWithoutBegin()
    {
        $adapter = $this->getMockedAdapter();
        $driver = $this->getMockedDriver();
        $connection = $this->getMockedConnection();

        $adapter->expects($this->any())
            ->method('getDriver')
            ->willReturn($driver);

        $driver->expects($this->any())
            ->method('getConnection')
            ->willReturn($connection);

        $connection->expects($this->never())
            ->method('rollbackTransaction');

        $this->setExpectedException('SFM\Transaction\TransactionException', "Can't rollback transaction while no one is running");

        $t = new DatabaseProvider($adapter);
        $t->rollbackTransaction();
    }
} 