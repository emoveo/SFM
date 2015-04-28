<?php

use SFM\IdentityMap\IdentityMap;

class IdentityMapTransactionTest extends PHPUnit_Framework_TestCase
{
    public function testTransactionEmptyAddAndCommit()
    {
        $entity1 = $this->getMockBuilder('SFM\Entity')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->setMockClassName('SampleEntity')
            ->getMock();

        $entity1->expects($this->any())
            ->method('getId')
            ->willReturn('1');

        $im = new IdentityMap(new \SFM\IdentityMap\IdentityMapStorage(), new \SFM\IdentityMap\IdentityMapStorage(), new \SFM\IdentityMap\IdentityMapStorage());
        $im->beginTransaction();
        $im->addEntity($entity1);
        $im->commitTransaction();

        $entityReturned = $im->getEntity('SampleEntity', 1);

        $this->assertInstanceOf('SampleEntity', $entityReturned);
        $this->assertSame($entityReturned, $entity1);
    }

    public function testTransactionRewriteAddAndCommit()
    {
        $entity1 = $this->getMockBuilder('SFM\Entity')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->setMockClassName('SampleEntity')
            ->getMock();

        $entity1->expects($this->any())
            ->method('getId')
            ->willReturn('1');

        $entity2 = $this->getMockBuilder('SFM\Entity')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->setMockClassName('SampleEntity')
            ->getMock();

        $entity2->expects($this->any())
            ->method('getId')
            ->willReturn('1');

        $im = new IdentityMap(new \SFM\IdentityMap\IdentityMapStorage(), new \SFM\IdentityMap\IdentityMapStorage(), new \SFM\IdentityMap\IdentityMapStorage());
        $im->addEntity($entity1);
        $im->beginTransaction();
        $im->addEntity($entity2);
        $im->commitTransaction();

        $entityReturned = $im->getEntity('SampleEntity', 1);

        $this->assertInstanceOf('SampleEntity', $entityReturned);
        $this->assertSame($entityReturned, $entity2);
    }

    public function testTransactionEmptyAddAndRollback()
    {
        $entity1 = $this->getMockBuilder('SFM\Entity')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->setMockClassName('SampleEntity')
            ->getMock();

        $entity1->expects($this->any())
            ->method('getId')
            ->willReturn('1');

        $im = new IdentityMap(new \SFM\IdentityMap\IdentityMapStorage(), new \SFM\IdentityMap\IdentityMapStorage(), new \SFM\IdentityMap\IdentityMapStorage());
        $im->beginTransaction();
        $im->addEntity($entity1);
        $im->rollbackTransaction();

        $entityReturned = $im->getEntity('SampleEntity', 1);

        $this->assertNull($entityReturned);
    }

    public function testTransactionRewriteAddAndRollback()
    {
        $entity1 = $this->getMockBuilder('SFM\Entity')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->setMockClassName('SampleEntity')
            ->getMock();

        $entity1->expects($this->any())
            ->method('getId')
            ->willReturn('1');

        $entity2 = $this->getMockBuilder('SFM\Entity')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->setMockClassName('SampleEntity')
            ->getMock();

        $entity2->expects($this->any())
            ->method('getId')
            ->willReturn('1');

        $im = new IdentityMap(new \SFM\IdentityMap\IdentityMapStorage(), new \SFM\IdentityMap\IdentityMapStorage(), new \SFM\IdentityMap\IdentityMapStorage());
        $im->addEntity($entity1);
        $im->beginTransaction();
        $im->addEntity($entity2);
        $im->rollbackTransaction();

        $entityReturned = $im->getEntity('SampleEntity', 1);

        $this->assertInstanceOf('SampleEntity', $entityReturned);
        $this->assertSame($entityReturned, $entity1);
    }

    public function testTransactionEmptyAddAndRemove1()
    {
        $entity1 = $this->getMockBuilder('SFM\Entity')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->setMockClassName('SampleEntity')
            ->getMock();

        $entity1->expects($this->any())
            ->method('getId')
            ->willReturn('1');

        $im = new IdentityMap(new \SFM\IdentityMap\IdentityMapStorage(), new \SFM\IdentityMap\IdentityMapStorage(), new \SFM\IdentityMap\IdentityMapStorage());
        $im->beginTransaction();
        $im->addEntity($entity1);
        $im->deleteEntity($entity1);
        $im->commitTransaction();

        $entityReturned = $im->getEntity('SampleEntity', 1);

        $this->assertNull($entityReturned);
    }

    public function testTransactionEmptyAddAndRemove2()
    {
        $entity1 = $this->getMockBuilder('SFM\Entity')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->setMockClassName('SampleEntity')
            ->getMock();

        $entity1->expects($this->any())
            ->method('getId')
            ->willReturn('1');

        $im = new IdentityMap(new \SFM\IdentityMap\IdentityMapStorage(), new \SFM\IdentityMap\IdentityMapStorage(), new \SFM\IdentityMap\IdentityMapStorage());
        $im->addEntity($entity1);
        $im->beginTransaction();
        $im->deleteEntity($entity1);
        $im->commitTransaction();

        $entityReturned = $im->getEntity('SampleEntity', 1);

        $this->assertNull($entityReturned);
    }

    public function testTransactionEmptyAddAndRemove3()
    {
        $entity1 = $this->getMockBuilder('SFM\Entity')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->setMockClassName('SampleEntity')
            ->getMock();

        $entity1->expects($this->any())
            ->method('getId')
            ->willReturn('1');

        $im = new IdentityMap(new \SFM\IdentityMap\IdentityMapStorage(), new \SFM\IdentityMap\IdentityMapStorage(), new \SFM\IdentityMap\IdentityMapStorage());
        $im->addEntity($entity1);
        $im->beginTransaction();
        $im->deleteEntity($entity1);
        $im->rollbackTransaction();

        $entityReturned = $im->getEntity('SampleEntity', 1);

        $this->assertInstanceOf('SampleEntity', $entityReturned);
        $this->assertSame($entityReturned, $entity1);
    }

    public function testBeginCommitOk()
    {
        $im = new IdentityMap(new \SFM\IdentityMap\IdentityMapStorage(), new \SFM\IdentityMap\IdentityMapStorage(), new \SFM\IdentityMap\IdentityMapStorage());
        $im->beginTransaction();
        $this->assertEquals(true, $im->isTransaction());
        $im->commitTransaction();
        $this->assertEquals(false, $im->isTransaction());
    }

    public function testBeginRollbackOk()
    {
        $im = new IdentityMap(new \SFM\IdentityMap\IdentityMapStorage(), new \SFM\IdentityMap\IdentityMapStorage(), new \SFM\IdentityMap\IdentityMapStorage());
        $im->beginTransaction();
        $this->assertEquals(true, $im->isTransaction());
        $im->rollbackTransaction();
        $this->assertEquals(false, $im->isTransaction());
    }

    public function testBeginTwice()
    {
        $this->setExpectedException('SFM\Transaction\TransactionException', "Transaction already started");

        $im = new IdentityMap(new \SFM\IdentityMap\IdentityMapStorage(), new \SFM\IdentityMap\IdentityMapStorage(), new \SFM\IdentityMap\IdentityMapStorage());
        $im->beginTransaction();
        $im->beginTransaction();
    }

    public function testRollbackTwice()
    {
        $this->setExpectedException('SFM\Transaction\TransactionException', "Transaction already stopped");

        $im = new IdentityMap(new \SFM\IdentityMap\IdentityMapStorage(), new \SFM\IdentityMap\IdentityMapStorage(), new \SFM\IdentityMap\IdentityMapStorage());
        $im->beginTransaction();
        $im->rollbackTransaction();
        $im->rollbackTransaction();
    }

    public function testCommitTwice()
    {
        $this->setExpectedException('SFM\Transaction\TransactionException', "Transaction already stopped");

        $im = new IdentityMap(new \SFM\IdentityMap\IdentityMapStorage(), new \SFM\IdentityMap\IdentityMapStorage(), new \SFM\IdentityMap\IdentityMapStorage());
        $im->beginTransaction();
        $im->commitTransaction();
        $im->commitTransaction();
    }

    public function testCommitRollback1()
    {
        $this->setExpectedException('SFM\Transaction\TransactionException', "Transaction already stopped");

        $im = new IdentityMap(new \SFM\IdentityMap\IdentityMapStorage(), new \SFM\IdentityMap\IdentityMapStorage(), new \SFM\IdentityMap\IdentityMapStorage());
        $im->beginTransaction();
        $im->rollbackTransaction();
        $im->commitTransaction();
    }

    public function testCommitRollback2()
    {
        $this->setExpectedException('SFM\Transaction\TransactionException', "Transaction already stopped");

        $im = new IdentityMap(new \SFM\IdentityMap\IdentityMapStorage(), new \SFM\IdentityMap\IdentityMapStorage(), new \SFM\IdentityMap\IdentityMapStorage());
        $im->beginTransaction();
        $im->commitTransaction();
        $im->rollbackTransaction();
    }
}