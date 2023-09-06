<?php

namespace Test\AlphaSoft\AsLinkOrm\Model;

use AlphaSoft\AsLinkOrm\Mapping\Column;
use AlphaSoft\AsLinkOrm\Mapping\PrimaryKeyColumn;
use AlphaSoft\AsLinkOrm\Entity\HasEntity;
use Test\AlphaSoft\AsLinkOrm\Repository\UserRepository;

final class User extends HasEntity
{
    public function getPrimaryKeyValue(): ?int
    {
        return $this->get(self::getPrimaryKeyColumn());
    }

    static public function getRepositoryName(): string
    {
        return UserRepository::class;
    }

    public function getPosts(): \SplObjectStorage
    {
        return $this->hasMany(Post::class, ['user_id' => $this->getPrimaryKeyValue()]);
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
