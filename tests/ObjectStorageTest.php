<?php

namespace Test\AlphaSoft\AsLinkOrm;


use AlphaSoft\AsLinkOrm\Collection\ObjectStorage;
use PHPUnit\Framework\TestCase;
use Test\AlphaSoft\AsLinkOrm\Model\User;

class ObjectStorageTest extends TestCase
{
    public function testFind()
    {
        $collection = new ObjectStorage();
        $collection->attach(new \stdClass());
        $collection->attach(new \stdClass());

        $foundObject = $collection->find(function ($item) {
            return true;
        });

        $this->assertInstanceOf(\stdClass::class, $foundObject);
    }

    public function testFindReturnsNullIfNotFound()
    {
        $collection = new ObjectStorage();

        $foundObject = $collection->find(function ($item) {
            return true;
        });

        $this->assertNull($foundObject);
    }

    public function testFindBy()
    {
        $collection = new ObjectStorage();
        $object1 = new \stdClass();
        $object2 = new \stdClass();
        $collection->attach($object1);
        $collection->attach($object2);

        $foundObjects = $collection->filter(function ($item) use($object1) {
            return $item === $object1;
        });

        $this->assertCount(1, $foundObjects);
        $this->assertContains($object1, $foundObjects);
    }

    public function testFindByReturnsEmptyArrayIfNotFound()
    {
        $collection = new ObjectStorage();

        $foundObjects = $collection->filter(function ($item) {
            return true;
        });

        $this->assertEmpty($foundObjects);
    }

    public function testFirst()
    {
        $collection = new ObjectStorage();
        $object = new \stdClass();
        $collection->attach($object);

        $firstObject = $collection->first();

        $this->assertSame($object, $firstObject);
    }

    public function testFirstReturnsNullIfCollectionIsEmpty()
    {
        $collection = new ObjectStorage();

        $firstObject = $collection->first();

        $this->assertNull($firstObject);
    }

    public function testToArray()
    {
        $collection = new ObjectStorage();
        $object1 = new \stdClass();
        $object2 = new \stdClass();
        $collection->attach($object1);
        $collection->attach($object2);

        $array = $collection->toArray();

        $this->assertCount(2, $array);
        $this->assertContains($object1, $array);
        $this->assertContains($object2, $array);
    }

    public function testToArrayReturnsEmptyArrayIfCollectionIsEmpty()
    {
        $collection = new ObjectStorage();

        $array = $collection->toArray();

        $this->assertEmpty($array);
    }

    public function testIsEmptyReturnsTrueForEmptyObjectStorage()
    {
        $objectStorage = new ObjectStorage();
        $this->assertTrue($objectStorage->isEmpty());
    }

    public function testIsEmptyReturnsFalseForObjectStorageWithItems()
    {
        $object1 = new \stdClass();
        $object2 = new \stdClass();

        $objectStorage = new ObjectStorage();
        $objectStorage->attach($object1);
        $objectStorage->attach($object2);

        $this->assertFalse($objectStorage->isEmpty());
    }

    public function testFindPkWithNullPrimaryKey()
    {
        $collection = new ObjectStorage();
        $result = $collection->findPk(null);
        $this->assertNull($result);
    }

    public function testFindPkWithNonExistentPrimaryKey()
    {
        $collection = new ObjectStorage();
        $result = $collection->findPk(999);
        $this->assertNull($result);
    }

    public function testFindPkWithExistingPrimaryKey()
    {
        $collection = new ObjectStorage();
        $object = new User(['id' => 133]);
        $collection->attach($object);
        $result = $collection->findPk(133);
        $this->assertSame($object, $result);
    }

    public function testFindOneBy()
    {
        $user = new User(['firstname' => 'John', 'lastname' => 'Doe']);
        $objectStorage = new ObjectStorage();
        $objectStorage->attach($user);
        $foundObject = $objectStorage->findOneBy('getFirstname', 'John');
        $this->assertEquals($user, $foundObject);

        $foundObject = $objectStorage->findOneBy('getNonExistentMethod', 'John');
        $this->assertNull($foundObject);
    }
}
