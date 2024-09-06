<?php

namespace AlphaSoft\AsLinkOrm\Platform;

use AlphaSoft\AsLinkOrm\AsLinkConnection;
use AlphaSoft\AsLinkOrm\Schema\AssqlSchema;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

class AssqlPlatform implements PlatformInterface
{
    private ?Connection $serverConnection = null;
    private array $params;
    public function __construct(private readonly AsLinkConnection $connection, private readonly AssqlSchema $schema)
    {
        $this->params = $connection->getParams();
    }

    public function getDatabaseName(): string
    {
        return $this->params['dbname'];
    }

    public function listTables(): array
    {
        $query = $this->connection->executeQuery($this->schema->showTables());
        $rows = $query->fetchAllAssociative();
        $tables = [];
        foreach ($rows as $row) {
            $tables[] = $row['tabname'];
        }
        return $tables;
    }

    public function listTableColumns(string $tableName): array
    {
        $query = $this->connection->executeQuery($this->schema->showTableColumns($tableName));
        $rows = $query->fetchAllAssociative();
        $columns = [];
        foreach ($rows as $row) {
            $columns[] = [
                'name' => $row['field'],
                'type' => $row['type'],
                'null' => $row['null'] == 'YES',
                'default' => $row['default'] ?? null,
                'comment' => $row['comment'] ?? null,
                'extra' => $row['extra'] ?? null,
                'attributes' => $row['attributes'] ?? null,
            ];
        }
        return $columns;
    }

    public function listDatabases(): array
    {
        $query = $this->getServerConnection()->executeQuery($this->schema->showDatabases());
        $data = $query->fetchAllAssociative();
        $this->getServerConnection()->close();
        return $data;
    }
    public function createDatabase(): void
    {
        $this->getServerConnection()->executeQuery($this->schema->createDatabase($this->getDatabaseName()));
        $this->getServerConnection()->close();
    }
    public function createDatabaseIfNotExists(): void
    {
        $this->getServerConnection()->executeQuery($this->schema->createDatabaseIfNotExists($this->getDatabaseName()));
        $this->getServerConnection()->close();
    }

    public function dropDatabase(): void
    {
        $this->getServerConnection()->executeQuery($this->schema->dropDatabase($this->getDatabaseName()));
        $this->getServerConnection()->close();
    }

    public function createTable(string $tableName, array $columns, array $options = []): int
    {
        return $this->connection->executeStatement($this->schema->createTable($tableName, $columns, $options));
    }

    public function addColumn(string $tableName, string $columnName, string $columnType): int
    {
        return $this->connection->executeStatement($this->schema->addColumn($tableName,$columnName, $columnType));
    }

    public function dropTable(string $tableName): int
    {
        return $this->connection->executeStatement($this->schema->dropTable($tableName));
    }

    public function dropColumn(string $tableName, string $columnName): int
    {
        return $this->connection->executeStatement($this->schema->dropColumn($tableName, $columnName));
    }

    public function renameColumn(string $tableName, string $oldColumnName, string $newColumnName): int
    {
        return $this->connection->executeStatement($this->schema->renameColumn($tableName, $oldColumnName, $newColumnName));
    }

    private function getServerConnection(): Connection
    {
        if ($this->serverConnection === null) {
            $params = $this->params;
            $params['wrapperClass'] = get_class($this->connection);
            $params['dbname'] = 'null';
            $this->serverConnection = DriverManager::getConnection($params);
            $sqlDebugger = $this->connection->getSqlDebugger();
            if ($sqlDebugger) {
                $this->serverConnection->enableDebugger($sqlDebugger);
            }
        }

        if (!$this->serverConnection->isConnected()) {
            $this->serverConnection->connect();
        }
        return $this->serverConnection;
    }
}
