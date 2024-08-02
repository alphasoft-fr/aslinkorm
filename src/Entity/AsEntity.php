<?php

namespace AlphaSoft\AsLinkOrm\Entity;

use AlphaSoft\AsLinkOrm\Collection\ObjectStorage;
use AlphaSoft\AsLinkOrm\Coordinator\EntityRelationCoordinator;
use AlphaSoft\AsLinkOrm\EntityManager;
use AlphaSoft\AsLinkOrm\Mapper\ColumnMapper;
use AlphaSoft\AsLinkOrm\Mapper\EntityMapper;
use AlphaSoft\AsLinkOrm\Mapping\Column;
use AlphaSoft\AsLinkOrm\Mapping\JoinColumn;
use AlphaSoft\AsLinkOrm\Mapping\OneToMany;
use AlphaSoft\AsLinkOrm\Serializer\SerializerToDb;
use AlphaSoft\AsLinkOrm\Serializer\SerializerToDbForUpdate;
use AlphaSoft\DataModel\Model;
use LogicException;
use ReflectionAttribute;
use ReflectionClass;

abstract class AsEntity extends Model
{
    /**
     * @var null|EntityRelationCoordinator
     */
    private $__relationCoordinator = null;

    private $__modifiedAttributes = [];

    public function __construct(array $data = [])
    {
        $this->attributes = static::getDefaultAttributes();
        $this->hydrate($data);
    }

    final public function hydrate(array $data): void
    {
        foreach ($data as $property => $value) {
            $this->set($property, $value, false);
        }
    }

    final public function set(string $property, $value, bool $update = true): static
    {
        $property = static::mapColumnToProperty($property);
        $column = static::getColumnByProperty($property);
        if ($column) {
            $value = $column->convertToPHP($value);
        }
        if ($update && $value !== $this->getOrNull($property)) {
            $this->__modifiedAttributes[$property] = $value;
        }

        $this->attributes[$property] = $value;

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
        return $this->__modifiedAttributes;
    }

    public function clearModifiedAttributes(): void
    {
        $this->__modifiedAttributes = [];
    }

    /**
     * Convert the object to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = [];
        foreach ($this->attributes as $property => $value) {
            $data[$property] = $value;
            if (is_iterable($value)) {
                $data[$property] = iterator_to_array($value);
                continue;
            }

            if ($value instanceof AsEntity) {
                $data[$property] = $value->toArray();
            }
        }
        return $data;
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


    protected function getEntityManager(): ?EntityManager
    {
        return $this->__relationCoordinator?->getEntityManager();
    }

    public function setRelatedOne(string $property, AsEntity $entity): void
    {
        $relationColumn = static::getJoinColumn($property);
        $targetEntity = $relationColumn->getTargetEntity();
        if ($targetEntity !== get_class($entity)) {
            throw new LogicException(static::class . " The target entity must be '$targetEntity'.");
        }

        if ($entity->getPrimaryKeyValue() === null) {
            throw new LogicException(static::class . " The target entity must have a primary key.");
        }

        $this->set($relationColumn->getProperty(), $entity->getPrimaryKeyValue());
        $this->set($relationColumn->getFictiveProperty(), $entity);
        $this->set($this->getKeyInitString($property), true);
    }

    public function getRelatedOne(string $property): ?object
    {
        if (!$this->has($property)) {
            throw new LogicException("No relation defined for the property '$property'.");
        }

        $keyInit = $this->getKeyInitString($property);
        if ($this->has($keyInit)) {
            return $this->get($property);
        }

        $relationColumn = static::getJoinColumn($property);
        $referencedColumnName = $relationColumn->getReferencedColumnName();
        $name = $relationColumn->getName();
        $targetEntity = $relationColumn->getTargetEntity();
        if (!is_subclass_of($targetEntity, AsEntity::class)) {
            throw new LogicException(static::class . " The target entity '$targetEntity' must be a subclass of AsEntity.");
        }

        $primaryKeyColumn = $targetEntity::getPrimaryKeyColumn();
        if ($primaryKeyColumn !== $referencedColumnName) {
            throw new LogicException(static::class . " The referenced column '$referencedColumnName' must be the primary key of the target entity '$targetEntity'.");
        }

        $this->set($property, $this->findPk($targetEntity, $this->get($name)));
        $this->set($keyInit, true);

        return $this->get($property);
    }


    public function getRelatedMany(string $property): ObjectStorage
    {
        if (!$this->has($property)) {
            throw new LogicException("No OneToMany relation defined for the property '$property'.");
        }

        $keyInit = $this->getKeyInitString($property);
        if ($this->has($keyInit)) {
            return $this->get($property);
        }

        $storage = $this->get($property);
        if (!$storage instanceof ObjectStorage) {
            throw new LogicException(static::class . " The property '$property' must be an instance of ObjectStorage.");
        }

        $oneToManyColumn = null;
        foreach (static::getOneToManyRelations() as $oneToManyRelation) {
            if ($oneToManyRelation->getProperty() === $property) {
                $oneToManyColumn = $oneToManyRelation;
                break;
            }
        }

        if ($oneToManyColumn === null) {
            throw new LogicException("No OneToMany relation defined for the property '$property'.");
        }

        $criteria = [];
        foreach ($oneToManyColumn->getCriteria() as $referencedColumnName => $value) {
            $criteria[$referencedColumnName] = $this->has($value) ? $this->get($value) : $value;
        }

        foreach ($this->hasMany($oneToManyColumn->getTargetEntity(), $criteria) as $object) {
            if ($storage->offsetExists($object)) {
                continue;
            }
            $storage->attach($object);
        }

        $this->set($keyInit, true);

        return $storage;
    }


    protected function findPk(string $relatedModel, ?int $pk, bool $forceRefresh = false): ?object
    {
        if ($pk === null) {
            return null;
        }

        if ($this->__relationCoordinator === null) {
            return null;
        }

        return $this->__relationCoordinator->findPk($relatedModel, $pk, $forceRefresh);
    }


    protected function hasOne(string $relatedModel, array $criteria = []): ?object
    {
        if ($this->__relationCoordinator === null) {
            return null;
        }

        return $this->__relationCoordinator->hasOne($relatedModel, $criteria);
    }

    protected function hasMany(string $relatedModel, array $criteria = []): ObjectStorage
    {
        if ($this->__relationCoordinator === null) {
            return new ObjectStorage();
        }

        return $this->__relationCoordinator->hasMany($relatedModel, $criteria);
    }

    final static protected function getDefaultAttributes(): array
    {
        $attributes = [];
        $columns = array_merge(self::getColumns(), self::getOneToManyRelations());
        foreach ($columns as $column) {
            $attributes[$column->getProperty()] = $column->getDefaultValue();
            if ($column instanceof JoinColumn) {
                $attributes[$column->getFictiveProperty()] = null;
            }
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
        return ColumnMapper::getPrimaryKeyColumn(static::class);
    }


    final static public function getJoinColumn(string $property): JoinColumn
    {
        return ColumnMapper::getJoinColumn(static::class, $property);
    }

    /**
     * @return array<OneToMany>
     */
    final static public function getOneToManyRelations(): array
    {
        return ColumnMapper::getOneToManyRelations(static::class);
    }

    /**
     * @return array<Column>
     */
    final static public function getColumns(): array
    {
        return ColumnMapper::getColumns(static::class);
    }

    final static public function getColumnByProperty(string $property): ?Column
    {
        return ColumnMapper::getColumnByProperty(static::class, $property);
    }

    static public function getTable(): string
    {
        return EntityMapper::getTable(static::class);
    }

    static public function getRepositoryName(): string
    {
        return EntityMapper::getRepositoryName(static::class);
    }

    private function getKeyInitString(string $property): string
    {
        return $property . '__init__';
    }


    static public function columnsMapping(): array
    {
        $reflector = new ReflectionClass(static::class);
        $columns = [];
        foreach ($reflector->getAttributes(Column::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            $columns[] = $attribute->newInstance();
        }
        foreach ($reflector->getAttributes(OneToMany::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            $columns[] = $attribute->newInstance();
        }
        return $columns;
    }
}
