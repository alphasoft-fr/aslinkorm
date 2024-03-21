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

    private $_relationsCache = [];

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function hasOne(string $relatedModel, array $criteria = [], bool $force = false): ?object
    {
        if (!is_subclass_of($relatedModel, AsEntity::class)) {
            throw new \LogicException("The related model '$relatedModel' must be a subclass of AsEntity.");
        }
        $key = md5($relatedModel.json_encode($criteria));
        if ($force || !array_key_exists($key, $this->_relationsCache)) {
            $this->_relationsCache[$key] = $this->getEntityManager()->getRepository($relatedModel::getRepositoryName())->findOneBy($criteria);
        }

        return $this->_relationsCache[$key];
    }

    public function hasMany(string $relatedModel, array $criteria = [], bool $force = false): ObjectStorage
    {
        if (!is_subclass_of($relatedModel, AsEntity::class)) {
            throw new \LogicException("The related model '$relatedModel' must be a subclass of AsEntity.");
        }
        $key = md5($relatedModel.json_encode($criteria));
        if ($force || !array_key_exists($key, $this->_relationsCache)) {
            $this->_relationsCache[$key] = $this->getEntityManager()->getRepository($relatedModel::getRepositoryName())->findBy($criteria);
        }
        return $this->_relationsCache[$key];
    }

    public function clearCache(): void
    {
        $this->_relationsCache = [];
    }

    private function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }
}
