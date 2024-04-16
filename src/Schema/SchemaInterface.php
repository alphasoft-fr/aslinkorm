<?php

namespace AlphaSoft\AsLinkOrm\Schema;

/**
 * Interface SchemaInterface
 *
 * Defines methods for managing database schema operations.
 */
interface SchemaInterface
{
    /**
     * Shows all databases.
     *
     * @return string Returns the SQL query for showing all databases.
     */
    public function showDatabases(): string;

    /**
     * Shows all tables in the database.
     *
     * @return string Returns the SQL query for showing all tables.
     */
    public function showTables(): string;

    public function showTableColumns(string $tableName): string;

    /**
     * Creates a new database.
     *
     * @param string $databaseName The name of the database to create.
     * @return string Returns the SQL query for creating the database.
     */
    public function createDatabase(string $databaseName): string;

    /**
     * Creates a new database if it does not exist.
     *
     * @param string $databaseName The name of the database to create.
     * @return string Returns the SQL query for creating the database if not exists.
     */
    public function createDatabaseIfNotExists(string $databaseName): string;

    /**
     * Drops an existing database.
     *
     * @param string $databaseName The name of the database to drop.
     * @return string Returns the SQL query for dropping the database.
     */
    public function dropDatabase(string $databaseName): string;

    /**
     * Creates a new table.
     *
     * @param string $tableName The name of the table to create.
     * @param array $columns An associative array of column definitions.
     * @param array $options Additional options for table creation.
     * @return string Returns the SQL query for creating the table.
     */
    public function createTable(string $tableName, array $columns, array $options = []): string;

    /**
     * Drops an existing table.
     *
     * @param string $tableName The name of the table to drop.
     * @return string Returns the SQL query for dropping the table.
     */
    public function dropTable(string $tableName): string;

    /**
     * Renames an existing table.
     *
     * @param string $oldTableName The current name of the table.
     * @param string $newTableName The new name for the table.
     * @return string Returns the SQL query for renaming the table.
     */
    public function renameTable(string $oldTableName, string $newTableName): string;

    /**
     * Adds a new column to an existing table.
     *
     * @param string $tableName The name of the table to modify.
     * @param string $columnName The name of the new column.
     * @param string $columnType The data type of the new column.
     * @return string Returns the SQL query for adding the column.
     */
    public function addColumn(string $tableName, string $columnName, string $columnType): string;

    /**
     * Drops an existing column from a table.
     *
     * @param string $tableName The name of the table to modify.
     * @param string $columnName The name of the column to drop.
     * @return string Returns the SQL query for dropping the column.
     */
    public function dropColumn(string $tableName, string $columnName): string;

    /**
     * Renames an existing column in a table.
     *
     * @param string $tableName The name of the table to modify.
     * @param string $oldColumnName The current name of the column.
     * @param string $newColumnName The new name for the column.
     * @return string Returns the SQL query for renaming the column.
     */
    public function renameColumn(string $tableName, string $oldColumnName, string $newColumnName): string;

    /**
     * Modifies the definition of an existing column in a table.
     *
     * @param string $tableName The name of the table to modify.
     * @param string $columnName The name of the column to modify.
     * @param string $newColumnType The new data type for the column.
     * @return string Returns the SQL query for modifying the column.
     */
    public function modifyColumn(string $tableName, string $columnName, string $newColumnType): string;

    /**
     * Creates a new unique index on a table.
     *
     * @param string $indexName The name of the index to create.
     * @param string $tableName The name of the table to index.
     * @param array $columns The columns to include in the index.
     * @return string Returns the SQL query for creating the index.
     */
    public function createUniqueIndex(string $indexName, string $tableName, array $columns): string;
    /**
     * Creates a new index on a table.
     *
     * @param string $indexName The name of the index to create.
     * @param string $tableName The name of the table to index.
     * @param array $columns The columns to include in the index.
     * @return string Returns the SQL query for creating the index.
     */
    public function createIndex(string $indexName, string $tableName, array $columns): string;

    /**
     * Drops an existing index from a table.
     *
     * @param string $indexName The name of the index to drop.
     * @param string|null $tableName The name of the table to modify.
     * @return string Returns the SQL query for dropping the index.
     */
    public function dropIndex(string $indexName, string $tableName = null): string;

    public function getDateTimeFormatString(): string;
    public function getDateFormatString(): string;
}
