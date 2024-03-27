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

    public function findPk(string $relatedModelClass, int $pk, bool $force = false): ?AsEntity
    {
        if (!is_subclass_of($relatedModelClass, AsEntity::class)) {
            throw new \LogicException("The related model '$relatedModelClass' must be a subclass of AsEntity.");
        }

        $repository = $this->getEntityManager()->getRepository($relatedModelClass::getRepositoryName());
        if ($force === false) {
            return $repository->findPkCache($pk);
        }
        return $repository->findPk($pk);
    }

    public function hasOne(string $relatedModelClass, array $criteria = [], bool $force = false): ?object
    {
        if (!is_subclass_of($relatedModelClass, AsEntity::class)) {
            throw new \LogicException("The related model '$relatedModelClass' must be a subclass of AsEntity.");
        }

        $repository = $this->getEntityManager()->getRepository($relatedModelClass::getRepositoryName());
        if ($force === false) {
            return $repository->findOneByCache($criteria);
        }
        return $repository->findOneBy($criteria);
    }

    public function hasMany(string $relatedModelClass, array $criteria = [], bool $force = false): ObjectStorage
    {
        if (!is_subclass_of($relatedModelClass, AsEntity::class)) {
            throw new \LogicException("The related model '$relatedModelClass' must be a subclass of AsEntity.");
        }

        $repository = $this->getEntityManager()->getRepository($relatedModelClass::getRepositoryName());
        if ($force === false) {
            return $repository->findByCache($criteria);
        }
        return $repository->findBy($criteria);
    }


    private function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }
}
