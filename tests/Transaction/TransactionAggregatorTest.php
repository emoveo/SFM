<?php

use SFM\Transaction\TransactionException;

class TransactionAggregatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * Two synced transaction engines registered in aggregator
     * Aggregator must return synced value
     */
    public function testSyncedEnginesState()
    {
        $transaction = new SFM\Transaction\TransactionAggregator();

        $engine1 = $this->getMockBuilder('SFM\Transaction\TransactionEngineInterface')
            ->disableOriginalConstructor()
            ->setMethods(['isTransaction', 'beginTransaction', 'commitTransaction', 'rollbackTransaction'])
            ->getMock();

        $engine1->expects($this->once())
                ->method('isTransaction')
                ->willReturn(true);

        $transaction->registerTransactionEngine($engine1);

        $engine2 = $this->getMockBuilder('SFM\Transaction\TransactionEngineInterface')
            ->disableOriginalConstructor()
            ->setMethods(['isTransaction', 'beginTransaction', 'commitTransaction', 'rollbackTransaction'])
            ->getMock();

        $engine2->expects($this->once())
            ->method('isTransaction')
            ->willReturn(true);

        $transaction->registerTransactionEngine($engine2);

        $this->assertEquals(true, $transaction->isTransaction());
    }

    /**
     * Two desynced transaction engines registered in aggregator
     * Aggregator must throw exception
     */
    public function testDesyncedEnginesState()
    {
        $transaction = new SFM\Transaction\TransactionAggregator();

        $engine1 = $this->getMockBuilder('SFM\Transaction\TransactionEngineInterface')
            ->disableOriginalConstructor()
            ->setMethods(['isTransaction', 'beginTransaction', 'commitTransaction', 'rollbackTransaction'])
            ->getMock();

        $engine1->expects($this->once())
            ->method('isTransaction')
            ->willReturn(true);

        $transaction->registerTransactionEngine($engine1);

        $engine2 = $this->getMockBuilder('SFM\Transaction\TransactionEngineInterface')
            ->disableOriginalConstructor()
            ->setMockClassName('DesyncedEngine')
            ->setMethods(['isTransaction', 'beginTransaction', 'commitTransaction', 'rollbackTransaction'])
            ->getMock();

        $engine2->expects($this->once())
            ->method('isTransaction')
            ->willReturn(false);

        $transaction->registerTransactionEngine($engine2);

        $this->setExpectedException('SFM\Transaction\TransactionException', 'Transaction engine `DesyncedEngine` is desynchronized from other last engine');
        $transaction->isTransaction();
    }

    /**
     * Two transaction engines must receive commands
     */
    public function testEnginesBeginOk()
    {
        $transaction = new SFM\Transaction\TransactionAggregator();

        $engine1 = $this->getMockBuilder('SFM\Transaction\TransactionEngineInterface')
            ->disableOriginalConstructor()
            ->setMethods(['isTransaction', 'beginTransaction', 'commitTransaction', 'rollbackTransaction'])
            ->getMock();

        $engine1->expects($this->at(0))
                ->method('beginTransaction');

        $transaction->registerTransactionEngine($engine1);

        $engine2 = $this->getMockBuilder('SFM\Transaction\TransactionEngineInterface')
            ->disableOriginalConstructor()
            ->setMethods(['isTransaction', 'beginTransaction', 'commitTransaction', 'rollbackTransaction'])
            ->getMock();

        $engine2->expects($this->at(0))
                ->method('beginTransaction');

        $transaction->registerTransactionEngine($engine2);
        $transaction->beginTransaction();
    }

    /**
     * Two transaction engines must receive commands
     * Second engine failed to start, must throw exception
     */
    public function testEnginesBeginNotOk()
    {
        $transaction = new SFM\Transaction\TransactionAggregator();

        $engine1 = $this->getMockBuilder('SFM\Transaction\TransactionEngineInterface')
            ->disableOriginalConstructor()
            ->setMethods(['isTransaction', 'beginTransaction', 'commitTransaction', 'rollbackTransaction'])
            ->getMock();

        $engine1->expects($this->at(0))
                ->method('beginTransaction');

        $engine1->expects($this->at(1))
                ->method('isTransaction')
                ->willReturn(true);

        $transaction->registerTransactionEngine($engine1);

        $engine2 = $this->getMockBuilder('SFM\Transaction\TransactionEngineInterface')
            ->disableOriginalConstructor()
            ->setMockClassName('FailedEngine')
            ->setMethods(['isTransaction', 'beginTransaction', 'commitTransaction', 'rollbackTransaction'])
            ->getMock();

        $engine2->expects($this->at(0))
                ->method('beginTransaction');

        $engine2->expects($this->at(1))
                ->method('isTransaction')
                ->willReturn(false);

        $transaction->registerTransactionEngine($engine2);

        $this->setExpectedException('SFM\Transaction\TransactionException', "Can't begin transaction on `FailedEngine` engine");

        $transaction->beginTransaction();
    }

    /**
     * Two transaction engines must receive commands
     */
    public function testEnginesCommitOk()
    {
        $transaction = new SFM\Transaction\TransactionAggregator();

        $engine1 = $this->getMockBuilder('SFM\Transaction\TransactionEngineInterface')
            ->disableOriginalConstructor()
            ->setMethods(['isTransaction', 'beginTransaction', 'commitTransaction', 'rollbackTransaction'])
            ->getMock();

        $engine1->expects($this->at(0))
                ->method('beginTransaction');

        $engine1->expects($this->at(1))
                ->method('commitTransaction');

        $transaction->registerTransactionEngine($engine1);

        $engine2 = $this->getMockBuilder('SFM\Transaction\TransactionEngineInterface')
            ->disableOriginalConstructor()
            ->setMethods(['isTransaction', 'beginTransaction', 'commitTransaction', 'rollbackTransaction'])
            ->getMock();

        $engine2->expects($this->at(0))
            ->method('beginTransaction');

        $engine2->expects($this->at(1))
            ->method('commitTransaction');

        $transaction->registerTransactionEngine($engine2);
        $transaction->beginTransaction();
        $transaction->commitTransaction();
    }

    /**
     * Two transaction engines must receive commands
     * Second engine failed to commit, must throw exception
     */
    public function testEnginesCommitNotOk()
    {
        $transaction = new SFM\Transaction\TransactionAggregator();

        $engine1 = $this->getMockBuilder('SFM\Transaction\TransactionEngineInterface')
            ->disableOriginalConstructor()
            ->setMethods(['isTransaction', 'beginTransaction', 'commitTransaction', 'rollbackTransaction'])
            ->getMock();

        $engine1->expects($this->at(0))
                ->method('beginTransaction');

        $engine1->expects($this->at(1))
                ->method('isTransaction')
                ->willReturn(true);

        $engine1->expects($this->at(2))
                ->method('commitTransaction');

        $engine1->expects($this->at(3))
                ->method('isTransaction')
                ->willReturn(false);

        $transaction->registerTransactionEngine($engine1);

        $engine2 = $this->getMockBuilder('SFM\Transaction\TransactionEngineInterface')
            ->disableOriginalConstructor()
            ->setMockClassName('FailedEngine')
            ->setMethods(['isTransaction', 'beginTransaction', 'commitTransaction', 'rollbackTransaction'])
            ->getMock();

        $engine2->expects($this->at(0))
                ->method('beginTransaction');

        $engine2->expects($this->at(1))
                ->method('isTransaction')
                ->willReturn(true);

        $engine2->expects($this->at(2))
                ->method('commitTransaction');

        $engine2->expects($this->at(3))
                ->method('isTransaction')
                ->willReturn(true);

        $transaction->registerTransactionEngine($engine2);

        $this->setExpectedException('SFM\Transaction\TransactionException', "Can't commit transaction on `FailedEngine` engine");

        $transaction->beginTransaction();
        $transaction->commitTransaction();
    }

    /**
     * Two transaction engines must receive commands
     */
    public function testEnginesRollbackOk()
    {
        $transaction = new SFM\Transaction\TransactionAggregator();

        $engine1 = $this->getMockBuilder('SFM\Transaction\TransactionEngineInterface')
            ->disableOriginalConstructor()
            ->setMethods(['isTransaction', 'beginTransaction', 'commitTransaction', 'rollbackTransaction'])
            ->getMock();

        $engine1->expects($this->at(0))
            ->method('beginTransaction');

        $engine1->expects($this->at(1))
            ->method('rollbackTransaction');

        $transaction->registerTransactionEngine($engine1);

        $engine2 = $this->getMockBuilder('SFM\Transaction\TransactionEngineInterface')
            ->disableOriginalConstructor()
            ->setMethods(['isTransaction', 'beginTransaction', 'commitTransaction', 'rollbackTransaction'])
            ->getMock();

        $engine2->expects($this->at(0))
            ->method('beginTransaction');

        $engine2->expects($this->at(1))
            ->method('rollbackTransaction');

        $transaction->registerTransactionEngine($engine2);
        $transaction->beginTransaction();
        $transaction->rollbackTransaction();
    }

    /**
     * Two transaction engines must receive commands
     * Second engine failed to commit, must throw exception
     */
    public function testEnginesRollbackNotOk()
    {
        $transaction = new SFM\Transaction\TransactionAggregator();

        $engine1 = $this->getMockBuilder('SFM\Transaction\TransactionEngineInterface')
            ->disableOriginalConstructor()
            ->setMethods(['isTransaction', 'beginTransaction', 'commitTransaction', 'rollbackTransaction'])
            ->getMock();

        $engine1->expects($this->at(0))
            ->method('beginTransaction');

        $engine1->expects($this->at(1))
            ->method('isTransaction')
            ->willReturn(true);

        $engine1->expects($this->at(2))
            ->method('rollbackTransaction');

        $engine1->expects($this->at(3))
            ->method('isTransaction')
            ->willReturn(false);

        $transaction->registerTransactionEngine($engine1);

        $engine2 = $this->getMockBuilder('SFM\Transaction\TransactionEngineInterface')
            ->disableOriginalConstructor()
            ->setMockClassName('FailedEngine')
            ->setMethods(['isTransaction', 'beginTransaction', 'commitTransaction', 'rollbackTransaction'])
            ->getMock();

        $engine2->expects($this->at(0))
            ->method('beginTransaction');

        $engine2->expects($this->at(1))
            ->method('isTransaction')
            ->willReturn(true);

        $engine2->expects($this->at(2))
            ->method('rollbackTransaction');

        $engine2->expects($this->at(3))
            ->method('isTransaction')
            ->willReturn(true);

        $transaction->registerTransactionEngine($engine2);

        $this->setExpectedException('SFM\Transaction\TransactionException', "Can't rollback transaction on `FailedEngine` engine");

        $transaction->beginTransaction();
        $transaction->rollbackTransaction();
    }

    public function testTwoTransactions()
    {
        $transaction = new SFM\Transaction\TransactionAggregator();

        $this->setExpectedException('SFM\Transaction\TransactionException', "Can't begin transaction while another one is running");

        $transaction->beginTransaction();
        $transaction->beginTransaction();
    }

    public function testCommitWithNoBegin()
    {
        $transaction = new SFM\Transaction\TransactionAggregator();

        $this->setExpectedException('SFM\Transaction\TransactionException', "Can't commit transaction while there is no transaction running");

        $transaction->commitTransaction();
    }

    public function testRollbackWithNoBegin()
    {
        $transaction = new SFM\Transaction\TransactionAggregator();

        $this->setExpectedException('SFM\Transaction\TransactionException', "Can't rollback transaction while there is no transaction running");

        $transaction->rollbackTransaction();
    }

    public function testBeginTransactionInternalEngineCrush()
    {
        $transaction = new SFM\Transaction\TransactionAggregator();
        $engine1 = $this->getMockBuilder('SFM\Transaction\TransactionEngineInterface')
            ->disableOriginalConstructor()
            ->setMockClassName('FailedEngine')
            ->setMethods(['isTransaction', 'beginTransaction', 'commitTransaction', 'rollbackTransaction'])
            ->getMock();

        $engine1->expects($this->once())
                ->method('beginTransaction')
                ->willThrowException(new TransactionException("Can't begin transaction on `FailedEngine` engine"));

        $transaction->registerTransactionEngine($engine1);

        $this->setExpectedException('SFM\Transaction\TransactionException', "Can't begin transaction on `FailedEngine` engine");

        $transaction->beginTransaction();
    }

    public function testCommitTransactionInternalEngineCrush()
    {
        $transaction = new SFM\Transaction\TransactionAggregator();
        $engine1 = $this->getMockBuilder('SFM\Transaction\TransactionEngineInterface')
            ->disableOriginalConstructor()
            ->setMockClassName('FailedEngine')
            ->setMethods(['isTransaction', 'beginTransaction', 'commitTransaction', 'rollbackTransaction'])
            ->getMock();

        $engine1->expects($this->at(0))
            ->method('beginTransaction');

        $engine1->expects($this->at(1))
            ->method('isTransaction')
            ->willReturn(true);

        $engine1->expects($this->at(2))
            ->method('commitTransaction')
            ->willThrowException(new TransactionException("Can't commit transaction on `FailedEngine` engine"));

        $transaction->registerTransactionEngine($engine1);

        $this->setExpectedException('SFM\Transaction\TransactionException', "Can't commit transaction on `FailedEngine` engine");

        $transaction->beginTransaction();
        $transaction->commitTransaction();
    }

    public function testRollbackTransactionInternalEngineCrush()
    {
        $transaction = new SFM\Transaction\TransactionAggregator();
        $engine1 = $this->getMockBuilder('SFM\Transaction\TransactionEngineInterface')
            ->disableOriginalConstructor()
            ->setMockClassName('FailedEngine')
            ->setMethods(['isTransaction', 'beginTransaction', 'commitTransaction', 'rollbackTransaction'])
            ->getMock();

        $engine1->expects($this->at(0))
            ->method('beginTransaction');

        $engine1->expects($this->at(1))
            ->method('isTransaction')
            ->willReturn(true);

        $engine1->expects($this->at(2))
            ->method('rollbackTransaction')
            ->willThrowException(new TransactionException("Can't commit transaction on `FailedEngine` engine"));

        $transaction->registerTransactionEngine($engine1);

        $this->setExpectedException('SFM\Transaction\TransactionException', "Can't rollback transaction on `FailedEngine` engine");

        $transaction->beginTransaction();
        $transaction->rollbackTransaction();
    }
} 