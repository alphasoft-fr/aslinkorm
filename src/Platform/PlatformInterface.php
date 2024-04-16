<?php

namespace AlphaSoft\AsLinkOrm\Platform;

/**
 * Interface PlatformInterface
 *
 * This interface defines methods for managing platform-specific database operations.
 */
interface PlatformInterface
{
    /**
     * Retrieves a list of all tables in the current database.
     *
     * @return array Returns an array containing the names of all tables in the database.
     */
    public function listTables(): array;
    public function listTableColumns(string $tableName): array;

    /**
     * Retrieves a list of all databases available on the platform.
     *
     * @return array Returns an array containing the names of all databases.
     */
    public function listDatabases(): array;

    /**
     * Creates a new database on the platform.
     *
     * @return void
     */
    public function createDatabase(): void;

    /**
     * Creates a new database on the platform if it does not already exist.
     *
     * @return void
     */
    public function createDatabaseIfNotExists(): void;

    /**
     * Retrieves the name of the current database.
     *
     * @return string Returns the name of the current database.
     */
    public function getDatabaseName(): string;

    /**
     * Drops the current database from the platform.
     *
     * @return void
     */
    public function dropDatabase(): void;

    public function createTable(string $tableName, array $columns, array $options = []): int;
    public function dropTable(string $tableName): int;
    public function addColumn(string $tableName, string $columnName, string $columnType): int;
    public function dropColumn(string $tableName, string $columnName): int;
    public function renameColumn(string $tableName, string $oldColumnName, string $newColumnName): int;
}

