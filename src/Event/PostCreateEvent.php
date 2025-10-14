<?php

namespace AlphaSoft\AsLinkOrm\Event;

use AlphaSoft\AsLinkOrm\Entity\AsEntity;
use AlphaSoft\AsLinkOrm\EntityManager;
use PhpDevCommunity\Listener\Event;

class PostCreateEvent extends Event
{
    private EntityManager $em;

    private AsEntity $entity;
    public function __construct(EntityManager $em, AsEntity $entity)
    {
        $this->em = $em;
        $this->entity = $entity;
    }

    public function getEntityManager(): EntityManager
    {
        return $this->em;
    }

    public function getEntity(): AsEntity
    {
        return $this->entity;
    }
}
