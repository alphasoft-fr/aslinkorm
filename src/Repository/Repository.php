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

abstract class Repository
{
    /**
     * @var EntityManager
     */
    protected $manager;

    /**
     * @var array<AsEntity>
     */
    private $entities = [];

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
    abstract public function getTableName(): string;

    /**
     * Get the name of the model associated with this repository.
     *
     * @return class-string<AsEntity> The name of the model.
     */
    abstract public function getEntityName(): string;

    public function findByCache(array $arguments = [], array $orderBy = [], ?int $limit = null)
    {
        $cacheKey = md5('many' . $this->getEntityName() . json_encode($arguments) . json_encode($orderBy) . json_encode($limit));
        if (!array_key_exists($cacheKey, $this->entities)) {
            $this->entities[$cacheKey] = $this->findBy($arguments, $orderBy, $limit);
        }
        return $this->entities[$cacheKey];
    }

    public function findOneByCache(array $arguments = [], array $orderBy = []): ?AsEntity
    {
        $cacheKey = md5('one' . $this->getEntityName() . json_encode($arguments) . json_encode($orderBy));
        if (!array_key_exists($cacheKey, $this->entities)) {
            $this->entities[$cacheKey] = $this->findOneBy($arguments, $orderBy);
        }
        return $this->entities[$cacheKey];
    }

    public function findPkCache(int $pk): ?AsEntity
    {
        if (!array_key_exists($pk, $this->entities)) {
            $this->entities[$pk] = $this->findPk($pk);
        }
        return $this->entities[$pk];
    }

    public function findPk(int $pk): ?AsEntity
    {
        /**
         * @var class-string<AsEntity> $entityName
         */
        $entityName = $this->getEntityName();
        $primaryKeyColumn = $entityName::getPrimaryKeyColumn();
        return $this->findOneBy([$primaryKeyColumn => $pk]);
    }

    public function findOneBy(array $arguments = [], array $orderBy = []): ?AsEntity
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
            throw new \LogicException('Cannot insert an entity with a primary key');
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
            $entity->set($primaryKeyColumn, ctype_digit($lastId) ? (int) $lastId : $lastId);
            $this->cache->set($entity->_getKey(), $entity);
            $entity->setEntityManager($this->manager);
        }
        return $rows;
    }

    public function update(AsEntity $entity, array $arguments = []): int
    {
        if ($entity->getPrimaryKeyValue() === null) {
            throw new \LogicException('Cannot update an entity without a primary key');
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
        $value =  $query->executeStatement();
        $this->cache->invalidate($entity->_getKey());
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
        unset($entity);

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
        /**
         * @var class-string<AsEntity> $entityName
         */
        $entityName = $this->getEntityName();

        $arguments = $this->mapPropertiesToColumn($arguments);
        $orderBy = $this->mapPropertiesToColumn($orderBy);
        $properties = array_map(function (Column $column) {
            return sprintf('`%s`', $column->getName());
        }, $entityName::getColumns());

        $query = $this->createQueryBuilder();
        $query
            ->select(...$properties)
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

    public function querySelect(string $alias = null): QueryBuilder
    {
        return $this->createQueryBuilder()->from($this->getTableName(), $alias);
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

    final protected function createModel(array $data): AsEntity
    {
        /**
         * @var class-string<AsEntity> $entityName
         */
        $entityName = $this->getEntityName();
        $primaryKeyValue = $data[$entityName::getPrimaryKeyColumn()];
        $cacheKey = $entityName.$primaryKeyValue;
        /**
         * * @var AsEntity $entity
         */
        if ($this->cache->has($cacheKey)) {
            $entity = $this->cache->get($cacheKey);
            $entity->hydrate($data);
        }else {
            $entity = ModelFactory::createModel($entityName, $data);
            $this->cache->set($entity->_getKey(), $entity);
        }

        $entity->setEntityManager($this->manager);
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


    public function clear(): void
    {
        $this->cache->clear();
    }
}
