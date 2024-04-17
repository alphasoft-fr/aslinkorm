<?php

namespace AlphaSoft\AsLinkOrm\Mapping;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Entity
{

    public function __construct(private string $table, private string $repositoryClass)
    {
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getRepositoryClass(): string
    {
        return $this->repositoryClass;
    }
}
