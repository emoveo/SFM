<?php

use SFM\IdentityMap\IdentityMapStorage;

class IdentityMapStorageTest extends PHPUnit_Framework_TestCase
{
    public function testPutAndGet()
    {
        $storage = new IdentityMapStorage();

        $entity = $this->getMockBuilder('SFM\Entity')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->setMockClassName('SampleEntity')
            ->getMock();

        $entity->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $storage->put($entity);

        $returnedEntity = $storage->get('SampleEntity', 1);
        $this->assertSame($entity, $returnedEntity);
    }

    public function testGetWithoutPut()
    {
        $storage = new IdentityMapStorage();

        $returnedEntity = $storage->get('SampleEntity', 1);
        $this->assertEquals(null, $returnedEntity);
    }

    public function testMultiplePutAndGet()
    {
        $storage = new IdentityMapStorage();

        $entity1 = $this->getMockBuilder('SFM\Entity')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->setMockClassName('SampleEntity')
            ->getMock();

        $entity1->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $storage->put($entity1);

        $entity2 = $this->getMockBuilder('SFM\Entity')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->setMockClassName('SampleEntity')
            ->getMock();

        $entity2->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $storage->put($entity2);

        $returnedEntity = $storage->get('SampleEntity', 1);
        $this->assertSame($entity2, $returnedEntity);
    }

    public function testMultiplePutAndGets()
    {
        $storage = new IdentityMapStorage();

        $entity1 = $this->getMockBuilder('SFM\Entity')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->setMockClassName('SampleEntity')
            ->getMock();

        $entity1->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $storage->put($entity1);

        $entity2 = $this->getMockBuilder('SFM\Entity')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->setMockClassName('SampleEntity')
            ->getMock();

        $entity2->expects($this->any())
            ->method('getId')
            ->willReturn(2);

        $storage->put($entity2);

        $returnedEntity1 = $storage->get('SampleEntity', 1);
        $this->assertSame($entity1, $returnedEntity1);

        $returnedEntity2 = $storage->get('SampleEntity', 2);
        $this->assertSame($entity2, $returnedEntity2);
    }

    public function testMultipleGet()
    {
        $storage = new IdentityMapStorage();

        $entity1 = $this->getMockBuilder('SFM\Entity')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->setMockClassName('SampleEntity')
            ->getMock();

        $entity1->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $storage->put($entity1);

        $entity2 = $this->getMockBuilder('SFM\Entity')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->setMockClassName('SampleEntity')
            ->getMock();

        $entity2->expects($this->any())
            ->method('getId')
            ->willReturn(3);

        $storage->put($entity2);

        $returnedEntities = $storage->getM('SampleEntity', [1, 3]);
        $this->assertEquals(2, count($returnedEntities));

        $this->assertSame($entity1, $returnedEntities[1]);
        $this->assertSame($entity2, $returnedEntities[3]);
    }

    public function testMultipleGetWithAbsentEntities()
    {
        $storage = new IdentityMapStorage();

        $entity1 = $this->getMockBuilder('SFM\Entity')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->setMockClassName('SampleEntity')
            ->getMock();

        $entity1->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $storage->put($entity1);

        $entity2 = $this->getMockBuilder('SFM\Entity')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->setMockClassName('SampleEntity')
            ->getMock();

        $entity2->expects($this->any())
            ->method('getId')
            ->willReturn(3);

        $storage->put($entity2);

        $returnedEntities = $storage->getM('SampleEntity', [1, 4, 5]);
        $this->assertEquals(1, count($returnedEntities));

        $this->assertSame($entity1, $returnedEntities[1]);
    }

    public function testRemove()
    {
        $storage = new IdentityMapStorage();

        $entity1 = $this->getMockBuilder('SFM\Entity')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->setMockClassName('SampleEntity')
            ->getMock();

        $entity1->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $storage->put($entity1);

        $entity2 = $this->getMockBuilder('SFM\Entity')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->setMockClassName('SampleEntity')
            ->getMock();

        $entity2->expects($this->any())
            ->method('getId')
            ->willReturn(3);

        $storage->put($entity2);

        $storage->remove('SampleEntity', 1);

        $returnedEntities = $storage->getM('SampleEntity', [1, 3]);
        $this->assertEquals(1, count($returnedEntities));

        $this->assertSame($entity2, $returnedEntities[3]);
    }

    public function testGetClassNames()
    {
        $storage = new IdentityMapStorage();

        $entity1 = $this->getMockBuilder('SFM\Entity')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->setMockClassName('SampleEntity1')
            ->getMock();

        $entity1->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $storage->put($entity1);

        $entity2 = $this->getMockBuilder('SFM\Entity')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->setMockClassName('SampleEntity4')
            ->getMock();

        $entity2->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $storage->put($entity2);

        $this->assertEquals(['SampleEntity1', 'SampleEntity4'], $storage->getClassNames());
    }

    public function testGetMultiAll()
    {
        $storage = new IdentityMapStorage();

        $entity1 = $this->getMockBuilder('SFM\Entity')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->setMockClassName('SampleEntity')
            ->getMock();

        $entity1->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $storage->put($entity1);

        $entity2 = $this->getMockBuilder('SFM\Entity')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->setMockClassName('SampleEntity')
            ->getMock();

        $entity2->expects($this->any())
            ->method('getId')
            ->willReturn(3);

        $storage->put($entity2);

        $returnedEntities = $storage->getM('SampleEntity');
        $this->assertEquals(2, count($returnedEntities));

        $this->assertSame($entity1, $returnedEntities[1]);
        $this->assertSame($entity2, $returnedEntities[3]);
    }

    public function testFlush()
    {
        $storage = new IdentityMapStorage();

        $entity1 = $this->getMockBuilder('SFM\Entity')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->setMockClassName('SampleEntity')
            ->getMock();

        $entity1->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $storage->put($entity1);

        $entity2 = $this->getMockBuilder('SFM\Entity')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->setMockClassName('SampleEntity')
            ->getMock();

        $entity2->expects($this->any())
            ->method('getId')
            ->willReturn(3);

        $storage->put($entity2);
        $storage->flush();

        $returnedEntities = $storage->getM('SampleEntity');
        $this->assertEquals(0, count($returnedEntities));
    }
} 