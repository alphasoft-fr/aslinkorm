<?php

namespace Test\AlphaSoft\Sql\Repository;

use AlphaSoft\Sql\Repository\Repository;
use Test\AlphaSoft\Sql\Model\Post;
use Test\AlphaSoft\Sql\Model\User;

class PostRepository extends Repository
{
    public function getTableName(): string
    {
        return 'post';
    }

    public function getModelName(): string
    {
        return Post::class;
    }
}
