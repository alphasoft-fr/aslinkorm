<?php

namespace AlphaSoft\AsLinkOrm\Driver;

use AlphaSoft\AsLinkOrm\AsLinkConnection;
use AlphaSoft\AsLinkOrm\Platform\PlatformInterface;
use AlphaSoft\AsLinkOrm\Platform\AssqlPlatform;
use AlphaSoft\AsLinkOrm\Schema\AssqlSchema;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\PDO\Connection;
use Doctrine\DBAL\Driver\PDO\Exception;

final class AssqlDriver extends Driver\AbstractMySQLDriver implements DriverInterface
{
    /**
     * {@inheritdoc}
     *
     * @return Connection
     */
    public function connect(array $params): Connection
    {
        $driverOptions = $params['driverOptions'] ?? [];

        if (! empty($params['persistent'])) {
            $driverOptions[\PDO::ATTR_PERSISTENT] = true;
        }

        try {
            $pdo = new \PDO(
                $this->resolveDsn($params),
                $params['user'] ?? '',
                $params['password'] ?? '',
                $driverOptions
            );
        } catch (\PDOException $exception) {
            throw Exception::new($exception);
        }

        return new Connection($pdo);
    }

    private function resolveDsn(array $params): string
    {
        $dsn = "assql:";
        if (isset($params['host']) && $params['host'] !== '') {
            $dsn .= 'host=' . $params['host'] . ';';
        }

        if (isset($params['port'])) {
            $dsn .= 'port=' . $params['port'] . ';';
        }

        if (isset($params['dbname'])) {
            $dsn .= 'dbname=' . $params['dbname'] . ';';
        }

        if (isset($params['unix_socket'])) {
            $dsn .= 'unix_socket=' . $params['unix_socket'] . ';';
        }

        if (isset($params['charset'])) {
            $dsn .= 'charset=' . $params['charset'] . ';';
        }

        return $dsn;
    }

    public function createDatabasePlatform(AsLinkConnection $connection): PlatformInterface
    {
        return new AssqlPlatform($connection, $this->createDatabaseSchema());
    }

    public function createDatabaseSchema(): AssqlSchema
    {
        return new AssqlSchema();
    }
}
