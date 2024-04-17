<?php

namespace AlphaSoft\AsLinkOrm\Mapper;

use AlphaSoft\AsLinkOrm\Mapping\Entity;

final class EntityMapper
{
    static public function getTable(string $class): string
    {
        $reflector = new \ReflectionClass($class);
        $attributes = $reflector->getAttributes(Entity::class);

        if (count($attributes) === 0) {
            throw new \LogicException(sprintf('%s: At least one %s attribute is required.', $class, Entity::class));
        }

        if (count($attributes) > 1) {
            throw new \LogicException(sprintf('%s: Only one %s is allowed.', $class, Entity::class));
        }

        $table = $attributes[0]->getArguments()['table'] ?? null;
        if ($table === null) {
            throw new \LogicException('table is required');
        }
        return $table;
    }

    static public function getRepositoryName(string $class): string
    {
        $reflector = new \ReflectionClass($class);
        $attributes = $reflector->getAttributes(Entity::class);

        $repositoryClass = $attributes[0]->getArguments()['repositoryClass'] ?? null;
        if ($repositoryClass === null) {
            throw new \LogicException('repositoryClass is required');
        }
        return $repositoryClass;
    }
}
