<?php

namespace AlphaSoft\AsLinkOrm\Repository;

use AlphaSoft\AsLinkOrm\Cache\MemcachedCache;
use AlphaSoft\AsLinkOrm\Collection\ObjectStorage;
use AlphaSoft\AsLinkOrm\Entity\AsEntity;
use AlphaSoft\AsLinkOrm\EntityManager;
use AlphaSoft\AsLinkOrm\Helper\QueryHelper;
use AlphaSoft\AsLinkOrm\Mapping\Column;
use AlphaSoft\DataModel\Factory\ModelFactory;
use Doctrine\DBAL\Query\QueryBuilder;
use LogicException;

abstract class Repository
{
    /**
     * @var EntityManager
     */
    protected $manager;

    /**
     * @var MemcachedCache|null
     */
    private $cache;

    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
        $this->cache = $manager->getCache();
    }

    /**
     * Get the name of the table associated with this repository.
     *
     * @return string The name of the table.
     */
    public function getTableName(): string
    {
        /**
         * @var class-string<AsEntity> $entityName
         */
        $entityName = $this->getEntityName();
        return $entityName::getTable();
    }

    /**
     * Get the name of the model associated with this repository.
     *
     * @return class-string<AsEntity> The name of the model.
     */
    abstract public function getEntityName(): string;


    public function findPk(int $pk): ?object
    {
        /**
         * @var class-string<AsEntity> $entityName
         */
        $entityName = $this->getEntityName();
        $primaryKeyColumn = $entityName::getPrimaryKeyColumn();
        return $this->findOneBy([$primaryKeyColumn => $pk]);
    }

    public function findOneBy(array $arguments = [], array $orderBy = []): ?object
    {
        $query = $this->generateSelectQuery($arguments, $orderBy, null);
        $item = $query->fetchAssociative();
        if ($item === false) {
            return null;
        }
        return $this->createModel($item);
    }

    public function findBy(array $arguments = [], array $orderBy = [], ?int $limit = null): ObjectStorage
    {
        $query = $this->generateSelectQuery($arguments, $orderBy, $limit);
        $data = $query->fetchAllAssociative();

        return $this->createCollection($data);
    }

    public function insert(AsEntity $entity): int
    {
        if ($entity->getPrimaryKeyValue() !== null) {
            throw new LogicException(static::class . ' Cannot insert an entity with a primary key');
        }

        $connection = $this->manager->getConnection();
        $query = $connection->createQueryBuilder();
        $query->insert($this->getTableName());

        $primaryKeyColumn = $entity::getPrimaryKeyColumn();
        foreach ($entity->toDb() as $property => $value) {
            if (str_replace('`', '', $property) === $primaryKeyColumn) {
                continue;
            }
            $query->setValue($property, $query->createPositionalParameter($value, QueryHelper::typeOfValue($value)));
        }
        $rows = $query->executeStatement();
        $lastId = $connection->lastInsertId();
        if ($lastId !== false) {
            $entity->set($primaryKeyColumn, ctype_digit($lastId) ? (int)$lastId : $lastId);
            $this->cache->set($entity->_getKey(), $entity);
            $entity->setEntityManager($this->manager);
        }
        return $rows;
    }

    public function update(AsEntity $entity, array $arguments = []): int
    {
        if ($entity->getPrimaryKeyValue() === null) {
            throw new LogicException(static::class . ' Cannot update an entity without a primary key');
        }

        $query = $this->createQueryBuilder();
        $query->update($this->getTableName());

        $properties = $entity->toDbForUpdate();
        if ($properties === []) {
            return 0;
        }
        $primaryKeyColumn = $entity::getPrimaryKeyColumn();
        foreach ($properties as $property => $value) {
            if (str_replace('`', '', $property) === $primaryKeyColumn) {
                continue;
            }
            $query->set($property, $query->createPositionalParameter($value, QueryHelper::typeOfValue($value)));
        }
        QueryHelper::generateWhereQuery($query, array_merge([$primaryKeyColumn => $entity->getPrimaryKeyValue()], $this->mapPropertiesToColumn($arguments)));
        $value = $query->executeStatement();
        $this->cache->invalidate($entity->_getKey());
        $entity->clearModifiedAttributes();
        return $value;
    }

    public function delete(AsEntity $entity): int
    {
        if ($entity->getPrimaryKeyValue() === null) {
            return 0;
        }

        $connection = $this->manager->getConnection();
        $query = $connection->createQueryBuilder();
        $query->delete($this->getTableName())
            ->where($entity::getPrimaryKeyColumn() . ' = ' . $query->createPositionalParameter($entity->getPrimaryKeyValue()));

        $this->cache->invalidate($entity->_getKey());
        $entity->set($entity::getPrimaryKeyColumn(), null);
        $entity->setEntityManager(null);

        return $query->executeStatement();
    }

    /**
     * @param array $arguments
     * @param array<string,string> $orderBy
     * @param int|null $limit
     * @return QueryBuilder
     */
    protected function generateSelectQuery(array $arguments = [], array $orderBy = [], ?int $limit = null): QueryBuilder
    {
        $arguments = $this->mapPropertiesToColumn($arguments);
        $orderBy = $this->mapPropertiesToColumn($orderBy);

        $query = $this->createQueryBuilder();
        $query
            ->select(...$this->getProperties())
            ->from($this->getTableName());
        QueryHelper::generateWhereQuery($query, $arguments);
        foreach ($orderBy as $property => $order) {
            $query->orderBy($property, $order);
        }
        $query->setMaxResults($limit);
        return $query;
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return $this->manager->getConnection()->createQueryBuilder();
    }

    public function queryUpdate(string $alias = null): QueryBuilder
    {
        return $this->createQueryBuilder()->update($this->getTableName(), $alias);
    }

    /**
     * Generates a query builder for selecting records from the table.
     *
     * @param string|null $alias The alias to be used for the table in the query.
     * @return QueryBuilder The query builder instance.
     */
    public function querySelect(string $alias = null): QueryBuilder
    {
        $properties = $this->getProperties();
        if ($alias !== null) {
            $properties = array_map(fn(string $property) => $alias . '.' . $property, $properties);
        }
        return $this->createQueryBuilder()
            ->select(...$properties)
            ->from($this->getTableName(), $alias);
    }

    final protected function mapPropertiesToColumn(array $arguments): array
    {
        /**
         * @var class-string<AsEntity> $entityName
         */
        $entityName = $this->getEntityName();
        $dbArguments = [];

        foreach ($arguments as $property => $value) {
            $column = $entityName::mapPropertyToColumn($property);
            $dbArguments[$column] = $value;
        }

        return $dbArguments;
    }

    final protected function createModel(array $data): object
    {
        $entityName = $this->getEntityName();
        $primaryKeyValue = $data[$entityName::getPrimaryKeyColumn()];
        $cacheKey = $entityName . $primaryKeyValue;
        if ($this->cache->has($cacheKey)) {
            $entity = $this->cache->get($cacheKey);
        } else {
            $entity = new $entityName();
            $this->cache->set($cacheKey, $entity);
        }

        if (method_exists($entity, 'hydrate')) {
            $entity->hydrate($data);
        }

        if (method_exists($entity, 'setEntityManager')) {
            $entity->setEntityManager($this->manager);
        }

        return $entity;
    }

    final protected function createCollection(array $dataCollection): ObjectStorage
    {
        $storage = new ObjectStorage();
        foreach ($dataCollection as $data) {
            $entity = $this->createModel($data);
            $storage->attach($entity);
        }
        return $storage;
    }

    /**
     * Get the properties of the entity.
     *
     * @return array The properties of the entity.
     */
    final protected function getProperties(): array
    {
        /**
         * @var class-string<AsEntity> $entityName
         */
        $entityName = $this->getEntityName();
        return array_map(function (Column $column) {
            return sprintf('`%s`', $column->getName());
        }, $entityName::getColumns());
    }


    public function clear(): void
    {
        $this->cache->clear();
    }
}
