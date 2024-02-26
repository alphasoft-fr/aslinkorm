<?php

namespace AlphaSoft\AsLinkOrm\Repository;

use AlphaSoft\DataModel\Model;
use AlphaSoft\DataModel\Factory\ModelFactory;
use AlphaSoft\AsLinkOrm\DoctrineManager;
use AlphaSoft\AsLinkOrm\Mapping\Column;
use AlphaSoft\AsLinkOrm\Entity\AsEntity;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use SplObjectStorage;
use function is_bool;
use function is_int;
use function is_null;

abstract class Repository
{
    /**
     * @var DoctrineManager
     */
    protected $manager;

    /**
     * @var array<AsEntity>
     */
    private $entities = [];

    public function __construct(DoctrineManager $manager)
    {
        $this->manager = $manager;
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

    public function findOneBy(array $arguments = [], array $orderBy = []): ?AsEntity
    {
        $query = $this->generateSelectQuery($arguments, $orderBy, null);
        $item = $query->fetchAssociative();
        if ($item === false) {
            return null;
        }
        return $this->createModel($item);
    }

    public function findBy(array $arguments = [], array $orderBy = [], ?int $limit = null): SplObjectStorage
    {
        $query = $this->generateSelectQuery($arguments, $orderBy, $limit);
        $data = $query->fetchAllAssociative();

        return $this->createCollection($data);
    }

    public function insert(AsEntity $entity): int
    {
        $connection = $this->manager->getConnection();
        $query = $connection->createQueryBuilder();
        $query->insert($this->getTableName());

        $primaryKeyColumn = $entity::getPrimaryKeyColumn();
        foreach ($entity->toDb() as $property => $value) {
            if ($property === $primaryKeyColumn) {
                continue;
            }
            $query->setValue($property, $query->createPositionalParameter($value, self::typeOfValue($value)));
        }
        $rows = $query->executeStatement();
        $lastId = $connection->lastInsertId();
        if ($lastId !== false) {
            $entity->set($primaryKeyColumn, $lastId);
            $this->entities[$entity->getPrimaryKeyValue()] = $entity;
            $entity->setDoctrineManager($this->manager);
        }
        return $rows;
    }

    public function update(AsEntity $entity, array $arguments = []): int
    {
        $query = $this->createQueryBuilder();
        $query->update($this->getTableName());

        $primaryKeyColumn = $entity::getPrimaryKeyColumn();
        foreach ($entity->toDbForUpdate() as $property => $value) {
            if ($property === $primaryKeyColumn) {
                continue;
            }
            $query->set($property, $query->createPositionalParameter($value, self::typeOfValue($value)));
        }
        self::generateWhereQuery($query, array_merge([$primaryKeyColumn => $entity->getPrimaryKeyValue()], $this->mapPropertiesToColumn($arguments)));
        return $query->executeStatement();
    }

    public function delete(AsEntity $entity): int
    {
        $connection = $this->manager->getConnection();
        $query = $connection->createQueryBuilder();
        $query->delete($this->getTableName())
            ->where($entity::getPrimaryKeyColumn() . ' = ' . $query->createPositionalParameter($entity->getPrimaryKeyValue()));

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
        self::generateWhereQuery($query, $arguments);
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

    protected static function generateWhereQuery(QueryBuilder $query, array $arguments = []): void
    {
        foreach ($arguments as $property => $value) {
            if (is_array($value)) {
                $query->andWhere($query->expr()->in($property, $query->createPositionalParameter($value, Connection::PARAM_STR_ARRAY)));
                continue;
            }
            $query->andWhere($property . ' = ' . $query->createPositionalParameter($value));
        }
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
        if (array_key_exists($primaryKeyValue, $this->entities)) {
            $entity = $this->entities[$primaryKeyValue];
            $entity->hydrate($data);  // Hydrate with new data
        } else {
            $entity = ModelFactory::createModel($this->getEntityName(), $data);
            $this->entities[$primaryKeyValue] = $entity;
        }

        if (is_subclass_of($entity, AsEntity::class)) {
            $entity->setDoctrineManager($this->manager);
        }
        return $entity;
    }

    final protected function createCollection(array $dataCollection): SplObjectStorage
    {
        $storage = new SplObjectStorage();
        foreach ($dataCollection as $data) {
            $entity = $this->createModel($data);
            $storage->attach($entity);
        }
        return $storage;
    }


    protected static function typeOfValue($value): int
    {
        $type = ParameterType::STRING;
        if (is_bool($value)) {
            $type = ParameterType::BOOLEAN;
        } elseif (is_int($value)) {
            $type = ParameterType::INTEGER;
        } elseif (is_null($value)) {
            $type = ParameterType::NULL;
        }
        return $type;
    }

    public function clear(): void
    {
        foreach ($this->entities as &$objet) {
            unset($objet);
        }
        $this->entities = [];
    }
}
