<?php

namespace AlphaSoft\AsLinkOrm\Entity;

use AlphaSoft\AsLinkOrm\Cache\ColumnCache;
use AlphaSoft\AsLinkOrm\Cache\PrimaryKeyColumnCache;
use AlphaSoft\AsLinkOrm\Collection\ObjectStorage;
use AlphaSoft\AsLinkOrm\Coordinator\EntityRelationCoordinator;
use AlphaSoft\AsLinkOrm\EntityManager;
use AlphaSoft\AsLinkOrm\Mapping\Column;
use AlphaSoft\AsLinkOrm\Mapping\PrimaryKeyColumn;
use AlphaSoft\AsLinkOrm\Serializer\SerializerToDb;
use AlphaSoft\AsLinkOrm\Serializer\SerializerToDbForUpdate;
use AlphaSoft\DataModel\Model;
use LogicException;

abstract class AsEntity extends Model
{
    /**
     * @var null|EntityRelationCoordinator
     */
    private $__relationCoordinator = null;

    private $_modifiedAttributes = [];

    final public function hydrate(array $data): void
    {
        foreach ($data as $property => $value) {
            parent::set($property, $value);
        }
    }

    final public function set(string $property, $value): Model
    {
        $property = static::mapColumnToProperty($property);
        if ($value !== $this->getOrNull($property)) {
            $this->_modifiedAttributes[$property] = $value;
        }
        parent::set($property, $value);

        return $this;
    }

    final public function has(string $property): bool
    {
        $property = static::mapColumnToProperty($property);
        return array_key_exists($property, $this->attributes);
    }

    final public function _getKey(): string
    {
        return static::class . $this->getPrimaryKeyValue();
    }

    public function getModifiedAttributes(): array
    {
        return $this->_modifiedAttributes;
    }

    final public function toDb(): array
    {
        return (new SerializerToDb($this))->serialize();
    }

    final public function toDbForUpdate(): array
    {
        return (new SerializerToDbForUpdate($this))->serialize();
    }

    public function setEntityManager(?EntityManager $manager): void
    {
        if ($manager === null) {
            $this->__relationCoordinator = null;
            return;
        }

        if ($this->__relationCoordinator instanceof EntityRelationCoordinator) {
            return;
        }

        $this->__relationCoordinator = new EntityRelationCoordinator($manager);
    }

    protected function findPk(string $relatedModel, ?int $pk, bool $force = false): ?object
    {
        if ($pk === null) {
            return null;
        }

        if ($this->__relationCoordinator === null) {
            return null;
        }

        return $this->__relationCoordinator->findPk($relatedModel, $pk, $force);
    }

    protected function hasOne(string $relatedModel, array $criteria = [], bool $force = false): ?object
    {
        if ($this->__relationCoordinator === null) {
            return null;
        }

        $attributeKey = md5($relatedModel . json_encode($criteria));
        if ($force === true || !$this->has($attributeKey)) {
            $this->set($attributeKey, $this->__relationCoordinator->hasOne($relatedModel, $criteria));
        }
        return $this->get($attributeKey);
    }

    protected function hasMany(string $relatedModel, array $criteria = [], bool $force = false): ObjectStorage
    {
        $attributeKey = md5($relatedModel . json_encode($criteria));
        if (!$this->has($attributeKey)) {
            $storage = new ObjectStorage();
            $this->set($attributeKey, $storage);
        }

        /**
         * @var ObjectStorage $storage
         */
        $storage = $this->get($attributeKey);

        if ($this->__relationCoordinator === null) {
            return $storage;
        }

        if ($force === false && !$storage->isEmpty()) {
            return $storage;
        }

        $storage->clear();
        foreach ($this->__relationCoordinator->hasMany($relatedModel, $criteria) as $object) {
            if ($storage->offsetExists($object)) {
                continue;
            }
            $storage->attach($object);
        }

        return $storage;
    }

    final static protected function getDefaultAttributes(): array
    {
        $attributes = [];
        foreach (self::getColumns() as $column) {
            $attributes[$column->getProperty()] = $column->getDefaultValue();
        }
        return $attributes;
    }

    final static protected function getDefaultColumnMapping(): array
    {
        $columns = [];
        foreach (self::getColumns() as $column) {
            $columns[$column->getProperty()] = $column->getName();
        }
        return $columns;
    }

    final static public function getPrimaryKeyColumn(): string
    {
        $cache = PrimaryKeyColumnCache::getInstance();
        if (empty($cache->get(static::class))) {

            $columnsFiltered = array_filter(self::getColumns(), function (Column $column) {
                return $column instanceof PrimaryKeyColumn;
            });

            if (count($columnsFiltered) === 0) {
                throw new LogicException('At least one primary key is required.');
            }

            if (count($columnsFiltered) > 1) {
                throw new LogicException('Only one primary key is allowed.');
            }

            $primaryKey = $columnsFiltered[0];

            $cache->set(static::class, $primaryKey);
        }
        return $cache->get(static::class)->getName();
    }

    /**
     * @return array<Column>
     */
    final static public function getColumns(): array
    {
        $cache = ColumnCache::getInstance();
        if (empty($cache->get(static::class))) {
            $cache->set(static::class, static::columnsMapping());
        }
        return $cache->get(static::class);
    }

    abstract static public function getRepositoryName(): string;

    abstract static protected function columnsMapping(): array;
}
