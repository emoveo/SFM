<?php

use SFM\IdentityMap\IdentityMap;

class IdentityMapTest extends PHPUnit_Framework_TestCase
{
    public function testAddEntity()
    {
        $storage = $this->getMockBuilder('SFM\IdentityMap\IdentityMapStorage')
            ->getMock();

        $storage1 = $this->getMockBuilder('SFM\IdentityMap\IdentityMapStorage')
            ->getMock();

        $storage2 = $this->getMockBuilder('SFM\IdentityMap\IdentityMapStorage')
            ->getMock();

        $im = new IdentityMap($storage, $storage1, $storage2);

        $entity = $this->getMockBuilder('SFM\Entity')
            ->disableOriginalConstructor()
            ->setMockClassName('SampleEntity')
            ->getMock();

        $storage->expects($this->once())
            ->method('put')
            ->with($this->equalTo($entity));

        $im->addEntity($entity);
    }

    public function testGet()
    {
        $storage = $this->getMockBuilder('SFM\IdentityMap\IdentityMapStorage')
            ->getMock();

        $storage1 = $this->getMockBuilder('SFM\IdentityMap\IdentityMapStorage')
            ->getMock();

        $storage2 = $this->getMockBuilder('SFM\IdentityMap\IdentityMapStorage')
            ->getMock();

        $im = new IdentityMap($storage, $storage1, $storage2);

        $storage->expects($this->once())
            ->method('get')
            ->with($this->equalTo('SampleEntity'), $this->equalTo(1));

        $im->getEntity('SampleEntity', 1);
    }

    public function testGetMultiple()
    {
        $storage = $this->getMockBuilder('SFM\IdentityMap\IdentityMapStorage')
            ->getMock();

        $storage1 = $this->getMockBuilder('SFM\IdentityMap\IdentityMapStorage')
            ->getMock();

        $storage2 = $this->getMockBuilder('SFM\IdentityMap\IdentityMapStorage')
            ->getMock();

        $storage2->expects($this->once())
            ->method('getM')
            ->with('SampleEntity')
            ->willReturn([]);

        $im = new IdentityMap($storage, $storage1, $storage2);

        $storage->expects($this->once())
            ->method('getM')
            ->with($this->equalTo('SampleEntity'), $this->equalTo([1, 3]))
            ->willReturn([]);

        $im->getEntityMulti('SampleEntity', [1, 3]);
    }

    public function testRemove()
    {
        $storage = $this->getMockBuilder('SFM\IdentityMap\IdentityMapStorage')
            ->getMock();

        $storage1 = $this->getMockBuilder('SFM\IdentityMap\IdentityMapStorage')
            ->getMock();

        $storage2 = $this->getMockBuilder('SFM\IdentityMap\IdentityMapStorage')
            ->getMock();

        $entity = $this->getMockBuilder('SFM\Entity')
            ->disableOriginalConstructor()
            ->setMockClassName('SampleEntity')
            ->getMock();

        $entity->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $im = new IdentityMap($storage, $storage1, $storage2);

        $storage->expects($this->once())
            ->method('remove')
            ->with($this->equalTo('SampleEntity'), $this->equalTo(1));

        $im->deleteEntity($entity);
    }

    public function testDisabledAddEntity()
    {
        $storage = $this->getMockBuilder('SFM\IdentityMap\IdentityMapStorage')
            ->getMock();

        $storage1 = $this->getMockBuilder('SFM\IdentityMap\IdentityMapStorage')
            ->getMock();

        $storage2 = $this->getMockBuilder('SFM\IdentityMap\IdentityMapStorage')
            ->getMock();

        $im = new IdentityMap($storage, $storage1, $storage2);
        $im->disable();

        $entity = $this->getMockBuilder('SFM\Entity')
            ->disableOriginalConstructor()
            ->setMockClassName('SampleEntity')
            ->getMock();

        $storage->expects($this->never())
            ->method('put');

        $im->addEntity($entity);
    }

    public function testDisabledGet()
    {
        $storage = $this->getMockBuilder('SFM\IdentityMap\IdentityMapStorage')
            ->getMock();

        $storage1 = $this->getMockBuilder('SFM\IdentityMap\IdentityMapStorage')
            ->getMock();

        $storage2 = $this->getMockBuilder('SFM\IdentityMap\IdentityMapStorage')
            ->getMock();

        $im = new IdentityMap($storage, $storage1, $storage2);
        $im->disable();

        $storage->expects($this->never())
            ->method('get');

        $entity = $im->getEntity('SampleEntity', 1);

        $this->assertEquals(null, $entity);
    }

    public function testDisabledGetMultiple()
    {
        $storage = $this->getMockBuilder('SFM\IdentityMap\IdentityMapStorage')
            ->getMock();

        $storage1 = $this->getMockBuilder('SFM\IdentityMap\IdentityMapStorage')
            ->getMock();

        $storage2 = $this->getMockBuilder('SFM\IdentityMap\IdentityMapStorage')
            ->getMock();

        $im = new IdentityMap($storage, $storage1, $storage2);
        $im->disable();

        $storage->expects($this->never())
            ->method('getM');

        $entities = $im->getEntityMulti('SampleEntity', [1, 3]);
        $this->assertEquals([], $entities);
    }

    public function testDisabledRemove()
    {
        $storage = $this->getMockBuilder('SFM\IdentityMap\IdentityMapStorage')
            ->getMock();

        $storage1 = $this->getMockBuilder('SFM\IdentityMap\IdentityMapStorage')
            ->getMock();

        $storage2 = $this->getMockBuilder('SFM\IdentityMap\IdentityMapStorage')
            ->getMock();

        $entity = $this->getMockBuilder('SFM\Entity')
            ->disableOriginalConstructor()
            ->setMockClassName('SampleEntity')
            ->getMock();

        $entity->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $im = new IdentityMap($storage, $storage1, $storage2);
        $im->disable();

        $storage->expects($this->never())
            ->method('remove');

        $im->deleteEntity($entity);
    }
} 