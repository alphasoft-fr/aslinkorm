<?php

namespace Test\AlphaSoft\AsLinkOrm\Model;

use AlphaSoft\AsLinkOrm\Collection\ObjectStorage;
use AlphaSoft\AsLinkOrm\Mapping\Column;
use AlphaSoft\AsLinkOrm\Mapping\PrimaryKeyColumn;
use AlphaSoft\AsLinkOrm\Entity\AsEntity;
use Test\AlphaSoft\AsLinkOrm\Repository\UserRepository;

final class User extends AsEntity
{
    public function getPrimaryKeyValue(): ?int
    {
        return $this->get(self::getPrimaryKeyColumn());
    }

    static public function getRepositoryName(): string
    {
        return UserRepository::class;
    }

    public function getPosts(): ObjectStorage
    {
        return $this->hasMany(Post::class, ['user_id' => $this->getPrimaryKeyValue()], true);
    }

    static protected function columnsMapping(): array
    {
        return [
            new PrimaryKeyColumn('id'),
            new Column('firstname'),
            new Column('lastname'),
            new Column('email'),
            new Column('password'),
            new Column('isActive', false , 'is_active'),
        ];
    }
}
