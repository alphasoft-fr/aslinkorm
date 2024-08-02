<?php

namespace AlphaSoft\AsLinkOrm\Coordinator;

use AlphaSoft\AsLinkOrm\Collection\ObjectStorage;
use AlphaSoft\AsLinkOrm\Entity\AsEntity;
use AlphaSoft\AsLinkOrm\EntityManager;

final class EntityRelationCoordinator
{
    /**
     * @var EntityManager
     */
    private $entityManager;


    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findPk(string $relatedModelClass, int $pk, bool $forceRefresh = false): ?object
    {
        if (!is_subclass_of($relatedModelClass, AsEntity::class)) {
            throw new \LogicException("The related model '$relatedModelClass' must be a subclass of AsEntity.");
        }

        $repository = $this->getEntityManager()->getRepository($relatedModelClass::getRepositoryName());
        $cache = $this->getEntityManager()->getCache();
        $cacheKey = $relatedModelClass.$pk;
        if ($forceRefresh === false && $cache->has($cacheKey)) {
            return $cache->get($cacheKey);
        }
        return $repository->findPk($pk);
    }

    public function hasOne(string $relatedModelClass, array $criteria = []): ?object
    {
        if (!is_subclass_of($relatedModelClass, AsEntity::class)) {
            throw new \LogicException("The related model '$relatedModelClass' must be a subclass of AsEntity.");
        }

        $repository = $this->getEntityManager()->getRepository($relatedModelClass::getRepositoryName());
        return $repository->findOneBy($criteria);
    }

    public function hasMany(string $relatedModelClass, array $criteria = []): ObjectStorage
    {
        if (!is_subclass_of($relatedModelClass, AsEntity::class)) {
            throw new \LogicException("The related model '$relatedModelClass' must be a subclass of AsEntity.");
        }
        $repository = $this->getEntityManager()->getRepository($relatedModelClass::getRepositoryName());
        return $repository->findBy($criteria);
    }


    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }
}
