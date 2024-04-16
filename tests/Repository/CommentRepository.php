<?php

namespace Test\AlphaSoft\AsLinkOrm\Repository;


use AlphaSoft\AsLinkOrm\Repository\Repository;
use Model\Comment;

class CommentRepository extends Repository
{
    public function getTableName(): string
    {
        return 'comment';
    }

    public function getEntityName(): string
    {
        return Comment::class;
    }
}
