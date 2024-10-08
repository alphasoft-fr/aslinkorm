<?php

namespace AlphaSoft\AsLinkOrm;

use AlphaSoft\AsLinkOrm\Cache\MemcachedCache;
use AlphaSoft\AsLinkOrm\Debugger\SqlDebugger;
use AlphaSoft\AsLinkOrm\Driver\DriverInterface;
use AlphaSoft\AsLinkOrm\Entity\AsEntity;
use AlphaSoft\AsLinkOrm\Platform\PlatformInterface;
use AlphaSoft\AsLinkOrm\Repository\Repository;
use Doctrine\DBAL\DriverManager;

class EntityManager
{
    /**
     * @var AsLinkConnection
     */
    private $connection;

    /**
     * @var UnitOfWork
     */
    private $unitOfWork;

    /**
     * @var MemcachedCache
     */
    private $cache;

    /**
     * @var array<Repository>
     */
    private $repositories = [];

    public function __construct(array $params)
    {
        $params['wrapperClass'] = AsLinkConnection::class;
        $this->connection = DriverManager::getConnection($params);
        $this->unitOfWork = new UnitOfWork();
        $this->cache = new MemcachedCache();
    }

    public function getConnection(): AsLinkConnection
    {
        return $this->connection;
    }

    public function getRepository(string $repository): Repository
    {
        if (is_subclass_of($repository, AsEntity::class)) {
            $repository = $repository::getRepositoryName();
        }

        if (!is_subclass_of($repository, Repository::class))  {
            throw new \InvalidArgumentException($repository. ' must be an instance of '.Repository::class);
        }

        if (!isset($this->repositories[$repository])) {
            $this->repositories[$repository] = new $repository($this);
        }
        return  $this->repositories[$repository];
    }

    public function persist(AsEntity $entity): void
    {
        $this->unitOfWork->persist($entity);
    }

    public function remove(AsEntity $entity): void
    {
        $this->unitOfWork->remove($entity);
    }

    public function flush(): void
    {
        foreach ($this->unitOfWork->getEntityInsertions() as $entity) {
            $repository = $this->getRepository(get_class($entity));
            $repository->insert($entity);
            $this->unitOfWork->unsetEntity($entity);
        }

        foreach ($this->unitOfWork->getEntityUpdates() as $entity) {
            $repository = $this->getRepository(get_class($entity));
            $repository->update($entity);
            $this->unitOfWork->unsetEntity($entity);
        }

        foreach ($this->unitOfWork->getEntityDeletions() as $entity) {
            $repository = $this->getRepository(get_class($entity));
            $repository->delete($entity);
            $this->unitOfWork->unsetEntity($entity);
        }

        $this->unitOfWork->clear();
    }

    public function clearAll(): void
    {
        $this->getCache()->clear();
    }

    public function getUnitOfWork(): UnitOfWork
    {
        return $this->unitOfWork;
    }

    public function getCache(): MemcachedCache
    {
        return $this->cache;
    }

    public function createDatabasePlatform(): PlatformInterface
    {
        $driver = $this->connection->getDriver();
        if ($driver instanceof DriverInterface) {
            return $driver->createDatabasePlatform($this->getConnection());
        }
        throw new \InvalidArgumentException(get_class($driver) . ' must implement the ' . DriverInterface::class . ' interface in order to create the database platform.');
    }
}
