<?php

namespace Test\AlphaSoft\AsLinkOrm\Repository;

use AlphaSoft\AsLinkOrm\Repository\Repository;
use Test\AlphaSoft\AsLinkOrm\Model\Post;

class PostRepository extends Repository
{

    public function getEntityName(): string
    {
        return Post::class;
    }
}
