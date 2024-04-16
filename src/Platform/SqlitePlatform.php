<?php

namespace AlphaSoft\AsLinkOrm\Platform;

use AlphaSoft\AsLinkOrm\AsLinkConnection;
use AlphaSoft\AsLinkOrm\Schema\SchemaInterface;
use AlphaSoft\AsLinkOrm\Schema\SqliteSchema;
use LogicException;

class SqlitePlatform implements PlatformInterface
{
    private array $params;

    public function __construct(private readonly AsLinkConnection $connection, private readonly SqliteSchema $schema)
    {
        $this->params = $connection->getParams();
    }

    public function getDatabaseName(): string
    {
        return "'main'";
    }

    public function listTables(): array
    {
        $query = $this->connection->executeQuery($this->schema->showTables());
        $rows = $query->fetchAllAssociative();
        $tables = [];
        foreach ($rows as $row) {
            $tables[] = $row['name'];
        }
        return $tables;
    }

    public function listTableColumns(string $tableName): array
    {
        $query = $this->connection->executeQuery($this->schema->showTableColumns($tableName));
        $rows = $query->fetchAllAssociative();
        $tables = [];
        foreach ($rows as $row) {
            $tables[] = [
                'name' => $row['name'],
                'type' => $row['type'],
            ];
        }
        return $tables;

    }

    public function listDatabases(): array
    {
        throw new LogicException(sprintf("The method '%s' is not supported by the platform interface.", __METHOD__));
    }

    public function createDatabase(): void
    {
        $database = $this->getDatabaseName();
        if (file_exists($database)) {
            return;
        }

        touch($database);
    }

    public function createDatabaseIfNotExists(): void
    {
        $this->createDatabase();
    }

    public function dropDatabase(): void
    {
        $database = $this->getDatabaseName();
        if (!file_exists($database)) {
            return;
        }

        unlink($database);
    }

    public function createTable(string $tableName, array $columns, array $options = []): int
    {
        return $this->connection->executeStatement($this->schema->createTable($tableName, $columns, $options));
    }

    public function dropTable(string $tableName): int
    {
        return $this->connection->executeStatement($this->schema->dropTable($tableName));
    }

    public function addColumn(string $tableName, string $columnName, string $columnType): int
    {
        return $this->connection->executeStatement($this->schema->addColumn($tableName,$columnName, $columnType));
    }

    public function dropColumn(string $tableName, string $columnName): int
    {
        return $this->connection->executeStatement($this->schema->dropColumn($tableName, $columnName));
    }

    public function renameColumn(string $tableName, string $oldColumnName, string $newColumnName): int
    {
        return $this->connection->executeStatement($this->schema->renameColumn($tableName, $oldColumnName, $newColumnName));
    }
}
