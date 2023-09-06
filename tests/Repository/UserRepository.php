<?php

namespace Test\AlphaSoft\Sql\Repository;

use AlphaSoft\Sql\Repository\Repository;
use Test\AlphaSoft\Sql\Model\User;

class UserRepository extends Repository
{
    public function getTableName(): string
    {
        return 'user';
    }

    public function getModelName(): string
    {
        return User::class;
    }
}
