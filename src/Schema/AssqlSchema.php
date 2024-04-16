<?php

namespace AlphaSoft\AsLinkOrm\Schema;

class AssqlSchema implements SchemaInterface
{

    public function showDatabases(): string
    {
        return 'SHOW DATABASES';
    }

    public function showTables(): string
    {
        return 'SHOW TABLES';
    }

    public function showTableColumns(string $tableName): string
    {
        return sprintf('SHOW COLUMNS FROM %s', $tableName);
    }

    public function createDatabase(string $databaseName): string
    {
        return sprintf('CREATE DATABASE "%s"', $databaseName);
    }

    public function createDatabaseIfNotExists(string $databaseName): string
    {
        return sprintf('CREATE DATABASE IF NOT EXISTS "%s"', $databaseName);
    }

    public function dropDatabase(string $databaseName): string
    {
        return sprintf('DROP DATABASE "%s"', $databaseName);
    }

    public function createTable(string $tableName, array $columns, array $options = []): string
    {
        $lines = [];
        foreach ($columns as $columnName => $columnType) {
            $lines[]  = "$columnName $columnType";
        }

        $linesString = implode(',', $lines);
        return sprintf('CREATE TABLE %s(%s)', $tableName, $linesString);
    }

    public function dropTable(string $tableName): string
    {
        return sprintf('DROP TABLE %s', $tableName);
    }

    public function renameTable(string $oldTableName, string $newTableName): string
    {
        return  sprintf('RENAME TABLE %s TO %s', $oldTableName, $newTableName);
    }

    public function addColumn(string $tableName, string $columnName, string $columnType): string
    {
        return sprintf('ALTER TABLE %s ADD (%s %s)', $tableName, $columnName, $columnType);
    }

    public function dropColumn(string $tableName, string $columnName): string
    {
        return sprintf('ALTER TABLE %s DROP (%s)', $tableName, $columnName);
    }

    public function renameColumn(string $tableName, string $oldColumnName, string $newColumnName): string
    {
        return sprintf('RENAME COLUMN %s.%s TO %s',$tableName, $oldColumnName, $newColumnName);
    }

    public function modifyColumn(string $tableName, string $columnName, string $newColumnType): string
    {
        return sprintf('ALTER TABLE %s MODIFY (%s %s)', $tableName, $columnName, $newColumnType);
    }

    public function createUniqueIndex(string $indexName, string $tableName, array $columns): string
    {
        return sprintf('CREATE UNIQUE INDEX %s ON %s (%s)', $indexName, $tableName, implode(', ', $columns));
    }
    public function createIndex(string $indexName, string $tableName, array $columns): string
    {
        return sprintf('CREATE INDEX %s ON %s (%s)', $indexName, $tableName, implode(', ', $columns));
    }

    public function dropIndex(string $indexName, string $tableName = null): string
    {
        return sprintf('DROP INDEX %s;', $indexName);
    }

    public function getDateTimeFormatString(): string
    {
        return 'd/m/Y H:i:s';
    }

    public function getDateFormatString(): string
    {
        return 'd/m/Y';
    }
}
