<?php

namespace AlphaSoft\Sql\Repository;

use AlphaSoft\DataModel\Model;
use AlphaSoft\DataModel\Factory\ModelFactory;
use AlphaSoft\Sql\DoctrineManager;
use AlphaSoft\Sql\Mapping\Column;
use AlphaSoft\Sql\Relation\HasEntity;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Test\AlphaSoft\Sql\Model\Post;

abstract class Repository
{
    /**
     * @var DoctrineManager
     */
    protected $manager;

    /**
     * @var array<HasEntity>
     */
    private $modelCache = [];

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
     * @return class-string<Model> The name of the model.
     */
    abstract public function getModelName(): string;

    public function findOneBy(array $arguments = [], array $orderBy = []): ?Model
    {
        $query = $this->generateSelectQuery($arguments, $orderBy, null);
        $item = $query->fetchAssociative();
        if ($item === false) {
            return null;
        }
        return $this->createModel($item);
    }

    public function findBy(array $arguments = [], array $orderBy = [], ?int $limit = null): \SplObjectStorage
    {
        $query = $this->generateSelectQuery($arguments, $orderBy, $limit);
        $data = $query->fetchAllAssociative();

        return $this->createCollection($data);
    }

    public function insert(HasEntity $model): int
    {
        $connection = $this->manager->getConnection();
        $query = $connection->createQueryBuilder();
        $query->insert($this->getTableName());

        $primaryKeyColumn = $model::getPrimaryKeyColumn();
        foreach ($model->toDb() as $property => $value) {
            if ($property === $primaryKeyColumn) {
                continue;
            }
            $query->setValue($property, $query->createPositionalParameter($value, self::typeOfValue($value)));
        }
        $rows = $query->executeStatement();
        $lastId = $connection->lastInsertId();
        if ($lastId !== false) {
            $model->set($primaryKeyColumn, $lastId);
            $this->modelCache[$model->getPrimaryKeyValue()] = $model;
        }
        return $rows;
    }

    public function update(HasEntity $model, array $arguments = []): int
    {
        $query = $this->createQueryBuilder();
        $query->update($this->getTableName());

        $primaryKeyColumn = $model::getPrimaryKeyColumn();
        foreach ($model->toDb() as $property => $value) {
            if ($property === $primaryKeyColumn) {
                continue;
            }
            $query->set($property, $query->createPositionalParameter($value, self::typeOfValue($value)));
        }
        self::generateWhereQuery($query, array_merge([$primaryKeyColumn => $model->getPrimaryKeyValue()], $this->mapPropertiesToColumn($arguments)));
        return $query->executeStatement();
    }

    public function delete(HasEntity $model): int
    {
        $connection = $this->manager->getConnection();
        $query = $connection->createQueryBuilder();
        $query->delete($this->getTableName())
            ->where($model::getPrimaryKeyColumn() . ' = ' . $query->createPositionalParameter($model->getPrimaryKeyValue()));

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
         * @var class-string<HasEntity> $modelClass
         */
        $modelClass = $this->getModelName();

        $arguments = $this->mapPropertiesToColumn($arguments);
        $properties = array_map(function (Column $column) {
            return $column->getName();
        }, $modelClass::getColumns());

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

    protected static function generateWhereQuery(QueryBuilder $query, array $arguments = []): void
    {
        foreach ($arguments as $property => $value) {
            if (is_array($value)) {
                $query->andWhere($query->expr()->in($property, $query->createPositionalParameter($value, ArrayParameterType::STRING)));
                continue;
            }
            $query->andWhere($property . ' = ' . $query->createPositionalParameter($value));
        }
    }

    final protected function mapPropertiesToColumn(array $arguments): array
    {
        /**
         * @var class-string<HasEntity> $modelClass
         */
        $modelClass = $this->getModelName();
        $dbArguments = [];

        foreach ($arguments as $property => $value) {
            $column = $modelClass::mapPropertyToColumn($property);
            $dbArguments[$column] = $value;
        }

        return $dbArguments;
    }

    final protected function createModel(array $data): Model {
        /**
         * @var class-string<HasEntity> $modelClass
         */
        $modelClass = $this->getModelName();
        $primaryKeyValue = $data[$modelClass::getPrimaryKeyColumn()];
        if (array_key_exists($primaryKeyValue, $this->modelCache)) {
            $model = $this->modelCache[$primaryKeyValue];
            $model->hydrate($data);  // Hydrate with new data
        }else {
            $model = ModelFactory::createModel($this->getModelName(), $data);
            $this->modelCache[$primaryKeyValue] = $model;
        }

        if (is_subclass_of($model, HasEntity::class)) {
            $model->setDoctrineManager($this->manager);
        }
        return $model;
    }

    final protected function createCollection(array $dataCollection): \SplObjectStorage
    {
        $storage = new \SplObjectStorage();
        foreach ($dataCollection as $data) {
            $model = $this->createModel($data);
            $storage->attach($model);
        }
        return $storage;
    }


    protected static function typeOfValue($value): int
    {
        $type = ParameterType::STRING;
        if (\is_bool($value)) {
            $type = ParameterType::BOOLEAN;
        } elseif (\is_int($value)) {
            $type = ParameterType::INTEGER;
        } elseif (\is_null($value)) {
            $type = ParameterType::NULL;
        }
        return $type;
    }
}
