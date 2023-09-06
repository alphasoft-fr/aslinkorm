<?php

namespace Test\AlphaSoft\AsLinkOrm\Repository;

use AlphaSoft\AsLinkOrm\Repository\Repository;
use Test\AlphaSoft\AsLinkOrm\Model\Post;

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
