<?php

namespace Test\AlphaSoft\AsLinkOrm\Model;

use AlphaSoft\AsLinkOrm\Mapping\Column;
use AlphaSoft\AsLinkOrm\Mapping\Entity;
use AlphaSoft\AsLinkOrm\Mapping\JoinColumn;
use AlphaSoft\AsLinkOrm\Mapping\PrimaryKeyColumn;
use AlphaSoft\AsLinkOrm\Entity\AsEntity;
use Test\AlphaSoft\AsLinkOrm\Repository\PostRepository;

#[Entity(table: "post", repositoryClass: PostRepository::class)]
#[PrimaryKeyColumn(property: 'id', type: 'int')]
#[Column(property: 'title')]
#[Column(property: 'content')]
#[JoinColumn(property: 'user', name: 'user_id', referencedColumnName: 'id', targetEntity: User::class)]
final class Post extends AsEntity
{
    public function getPrimaryKeyValue(): ?int
    {
        return $this->get(self::getPrimaryKeyColumn());
    }

    public function setUser(User $user): self
    {
        $this->setRelatedOne('user', $user);
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->getRelatedOne('user');
    }

    public function getUserHasOneMethod(): ?User
    {
        return $this->hasOne(User::class, ['id' => $this->get('user_id')]);
    }

//    static public function getRepositoryName(): string
//    {
//        return PostRepository::class;
//    }

//    static public function columnsMapping(): array
//    {
//        return [
//            new PrimaryKeyColumn('id'),
//            new Column('title'),
//            new Column('content'),
//            new JoinColumn('user', 'user_id', 'id',User::class),
//        ];
//    }
}
