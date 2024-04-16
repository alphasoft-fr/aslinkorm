<?php

namespace AlphaSoft\AsLinkOrm\Driver;

use AlphaSoft\AsLinkOrm\AsLinkConnection;
use AlphaSoft\AsLinkOrm\Platform\PlatformInterface;
use AlphaSoft\AsLinkOrm\Platform\SqlitePlatform;
use AlphaSoft\AsLinkOrm\Schema\SqliteSchema;
use Doctrine\DBAL\Driver\AbstractSQLiteDriver;
use Doctrine\DBAL\Driver\API\SQLite\UserDefinedFunctions;
use Doctrine\DBAL\Driver\PDO\Connection;
use Doctrine\DBAL\Driver\PDO\Exception;
use Doctrine\Deprecations\Deprecation;
use PDO;
use PDOException;

final class SqliteDriver extends AbstractSQLiteDriver implements DriverInterface
{
    public function connect(
        #[SensitiveParameter]
        array $params
    ): Connection
    {
        $driverOptions = $params['driverOptions'] ?? [];
        $userDefinedFunctions = [];

        if (isset($driverOptions['userDefinedFunctions'])) {
            Deprecation::trigger(
                'doctrine/dbal',
                'https://github.com/doctrine/dbal/pull/5742',
                'The SQLite-specific driver option "userDefinedFunctions" is deprecated.'
                . ' Register function directly on the native connection instead.',
            );

            $userDefinedFunctions = $driverOptions['userDefinedFunctions'];
            unset($driverOptions['userDefinedFunctions']);
        }

        try {
            $pdo = new PDO(
                $this->constructPdoDsn(array_intersect_key($params, ['path' => true, 'memory' => true])),
                $params['user'] ?? '',
                $params['password'] ?? '',
                $driverOptions,
            );
        } catch (PDOException $exception) {
            throw Exception::new($exception);
        }

        UserDefinedFunctions::register(
            [$pdo, 'sqliteCreateFunction'],
            $userDefinedFunctions,
        );

        return new Connection($pdo);
    }

    /**
     * Constructs the Sqlite PDO DSN.
     *
     * @param array<string, mixed> $params
     */
    private function constructPdoDsn(array $params): string
    {
        $dsn = 'sqlite:';
        if (isset($params['path'])) {
            $dsn .= $params['path'];
        } elseif (isset($params['memory'])) {
            $dsn .= ':memory:';
        }

        return $dsn;
    }

    public function createDatabasePlatform(AsLinkConnection $connection): PlatformInterface
    {
        return new SqlitePlatform($connection, $this->createDatabaseSchema());
    }

    public function createDatabaseSchema(): SqliteSchema
    {
        return new SqliteSchema();
    }
}
