<?php

namespace AlphaSoft\Sql\Relation;

use AlphaSoft\DataModel\Model;
use AlphaSoft\Sql\Cache\ColumnCache;
use AlphaSoft\Sql\Cache\PrimaryKeyColumnCache;
use AlphaSoft\Sql\DoctrineManager;
use AlphaSoft\Sql\Mapping\Column;
use AlphaSoft\Sql\Mapping\PrimaryKeyColumn;
use LogicException;
use SplObjectStorage;

abstract class HasEntity extends Model
{
    /**
     * @var null|DoctrineManager
     */
    private $__manager = null;

    public function setDoctrineManager(DoctrineManager $manager): void
    {
        $this->__manager = $manager;
    }

    protected function hasOne(string $relatedModel, array $criteria = []): ?HasEntity
    {
        if (!is_subclass_of($relatedModel, HasEntity::class)) {
            throw new LogicException("The related model '$relatedModel' must be a subclass of HasEntity.");
        }

        return $this->getManager()->getRepository($relatedModel::getRepositoryName())->findOneBy($criteria);
    }

    protected function hasMany(string $relatedModel, array $criteria = []): SplObjectStorage
    {
        if (!is_subclass_of($relatedModel, HasEntity::class)) {
            throw new LogicException("The related model '$relatedModel' must be a subclass of HasEntity.");
        }

        return $this->getManager()->getRepository($relatedModel::getRepositoryName())->findBy($criteria);
    }

    /**
     * @return DoctrineManager|null
     */
    private function getManager(): ?DoctrineManager
    {
        if ($this->__manager === null) {
            throw new LogicException(DoctrineManager::class . ' must be set before using this method.');
        }
        return $this->__manager;
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
