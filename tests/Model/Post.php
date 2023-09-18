<?php

namespace Test\AlphaSoft\AsLinkOrm\Model;

use AlphaSoft\AsLinkOrm\Mapping\Column;
use AlphaSoft\AsLinkOrm\Mapping\PrimaryKeyColumn;
use AlphaSoft\AsLinkOrm\Entity\HasEntity;
use Test\AlphaSoft\AsLinkOrm\Repository\PostRepository;

final class Post extends HasEntity
{
    public function getPrimaryKeyValue(): ?int
    {
        return $this->get(self::getPrimaryKeyColumn());
    }

    public function setUser(User $user): self
    {
        $this->set('user_id', $user->getPrimaryKeyValue());
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->hasOne(User::class, ['id' => $this->get('user_id')]);
    }

    static public function getRepositoryName(): string
    {
        return PostRepository::class;
    }

    static protected function columnsMapping(): array
    {
        return [
            new PrimaryKeyColumn('id'),
            new Column('title'),
            new Column('content'),
            new Column('user_id'),
        ];
    }
}
