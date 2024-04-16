<?php

namespace AlphaSoft\AsLinkOrm\Driver;

use AlphaSoft\AsLinkOrm\AsLinkConnection;
use AlphaSoft\AsLinkOrm\Platform\PlatformInterface;
use AlphaSoft\AsLinkOrm\Schema\SchemaInterface;
use Doctrine\DBAL\Driver\PDO\Connection;

interface DriverInterface
{
    public function connect(array $params): Connection;
    public function createDatabasePlatform(AsLinkConnection $connection): PlatformInterface;
    public function createDatabaseSchema(): SchemaInterface;
}
