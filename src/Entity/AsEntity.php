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
        $this->__relationCoordinator = $manager ? new EntityRelationCoordinator($manager) : null;
    }

    protected function hasOne(string $relatedModel, array $criteria = [], bool $force = true): ?object
    {
        return $this->__relationCoordinator ? $this->__relationCoordinator->hasOne($relatedModel, $criteria, $force) : null;
    }

    protected function hasMany(string $relatedModel, array $criteria = [], bool $force = true): ObjectStorage
    {
        return $this->__relationCoordinator ? $this->__relationCoordinator->hasMany($relatedModel, $criteria, $force) : new ObjectStorage();
    }

    public function clearRelationsCache(): void
    {
        if ($this->__relationCoordinator) {
            $this->__relationCoordinator->clearCache();
        }
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
