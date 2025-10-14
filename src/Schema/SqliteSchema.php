<?php

namespace AlphaSoft\AsLinkOrm\Schema;

class SqliteSchema implements SchemaInterface
{

    public function showDatabases(): string
    {
        throw new \LogicException(sprintf("The method '%s' is not supported by the schema interface.", __METHOD__));
    }

    public function showTables(): string
    {
        return "SELECT name FROM  sqlite_schema WHERE  type ='table' AND name NOT LIKE 'sqlite_%'";
    }

    public function showTableColumns(string $tableName): string
    {
        return sprintf("PRAGMA table_info('%s')", $tableName);
    }

    public function createDatabase(string $databaseName): string
    {
        throw new \LogicException(sprintf("The method '%s' is not supported by the schema interface.", __METHOD__));
    }

    public function createDatabaseIfNotExists(string $databaseName): string
    {
        throw new \LogicException(sprintf("The method '%s' is not supported by the schema interface.", __METHOD__));
    }

    public function dropDatabase(string $databaseName): string
    {
        throw new \LogicException(sprintf("The method '%s' is not supported by the schema interface.", __METHOD__));
    }

    public function createTable(string $tableName, array $columns, array $options = []): string
    {
        $lines = [];
        foreach ($columns as $columnName => $columnType) {
            $lines[]  = "$columnName $columnType";
        }

        foreach ($options as $option) {
            $lines[]  = $option;
        }

        $linesString = implode(',', $lines);

        return sprintf("CREATE TABLE $tableName (%s)", $linesString);
    }

    public function dropTable(string $tableName): string
    {
        return sprintf('DROP TABLE %s', $tableName);
    }

    public function renameTable(string $oldTableName, string $newTableName): string
    {
        return sprintf('ALTER TABLE %s RENAME TO %s', $oldTableName, $newTableName);
    }

    public function addColumn(string $tableName, string $columnName, string $columnType): string
    {
        return sprintf('ALTER TABLE %s ADD %s %s', $tableName, $columnName, $columnType);
    }

    public function dropColumn(string $tableName, string $columnName): string
    {
        if (!$this->supportsDropColumn()) {
            throw new \LogicException(sprintf("The method '%s' is not supported with SQLite versions older than 3.35.0.", __METHOD__));
        }

        return sprintf('ALTER TABLE %s DROP COLUMN %s', $tableName, $columnName);
    }

    public function renameColumn(string $tableName, string $oldColumnName, string $newColumnName): string
    {
        return sprintf('ALTER TABLE %s RENAME COLUMN %s to %s', $tableName, $oldColumnName, $newColumnName);
    }

    public function modifyColumn(string $tableName, string $columnName, string $newColumnType): string
    {
        throw new \LogicException(sprintf("The method '%s' is not supported by the schema interface.", __METHOD__));
    }

    public function createUniqueIndex(string $indexName, string $tableName, array $columns): string
    {
        return sprintf('CREATE UNIQUE INDEX %s ON %s (%s)',$indexName, $tableName, implode(', ', $columns));
    }

    public function createIndex(string $indexName, string $tableName, array $columns): string
    {
        return sprintf('CREATE INDEX %s ON %s (%s)',$indexName, $tableName, implode(', ', $columns));
    }

    public function dropIndex(string $indexName, string $tableName = null): string
    {
        return sprintf('DROP INDEX %s;', $indexName);
    }

    public function getDateTimeFormatString(): string
    {
        return 'Y-m-d H:i:s';
    }

    public function getDateFormatString(): string
    {
        return 'Y-m-d';
    }

    public function supportsDropColumn(): bool
    {
        return \SQLite3::version()['versionString'] >= '3.35.0';
    }

}
