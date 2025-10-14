<?php

namespace AlphaSoft\AsLinkOrm\Event;

use AlphaSoft\AsLinkOrm\Entity\AsEntity;
use AlphaSoft\AsLinkOrm\EntityManager;
use PhpDevCommunity\Listener\Event;

class PostUpdateEvent extends Event
{
    private EntityManager $em;

    private AsEntity $entity;
    private array $changes;

    public function __construct(EntityManager $em, AsEntity $entity, array $changes)
    {
        $this->em = $em;
        $this->entity = $entity;
        $this->changes = $changes;
    }

    public function getEntityManager(): EntityManager
    {
        return $this->em;
    }

    public function getEntity(): AsEntity
    {
        return $this->entity;
    }

    public function getChanges(): array
    {
        return $this->changes;
    }
}
