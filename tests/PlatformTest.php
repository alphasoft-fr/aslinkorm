<?php

namespace Test\AlphaSoft\AsLinkOrm;

use AlphaSoft\AsLinkOrm\Driver\SqliteDriver;
use AlphaSoft\AsLinkOrm\EntityManager;
use PDO;
use PHPUnit\Framework\TestCase;

class PlatformTest extends TestCase
{
    private EntityManager $manager;

    protected function setUp(): void
    {
        $manager = new EntityManager([
            'driver' => null,
            'driverClass' => SqliteDriver::class,
            'memory' => true,
            'driverOptions' => [PDO::ATTR_EMULATE_PREPARES => FALSE, PDO::FETCH_NUM => true]
        ]);

        $this->manager = $manager;
    }

    public function testCreateTables()
    {
        $platform = $this->manager->createDatabasePlatform();
        $platform->createTable('user', [
            'id' => 'INTEGER PRIMARY KEY',
            'firstname' => 'TEXT',
            'lastname' => 'TEXT',
            'email' => 'TEXT',
            'password' => 'TEXT',
            'is_active' => 'INTEGER',
        ]);

        $platform->createTable('post', [
            'id' => 'INTEGER PRIMARY KEY',
            'user_id' => 'INTEGER',
            'title' => 'TEXT',
            'content' => 'TEXT',
        ], [
            'FOREIGN KEY (user_id) REFERENCES user (id)'
        ]);

        $this->assertCount(2, $platform->listTables());
        $this->assertEquals(['user', 'post'], $platform->listTables());
    }

    public function testDropTable()
    {
        $platform = $this->manager->createDatabasePlatform();
        $platform->createTable('user', [
            'id' => 'INTEGER PRIMARY KEY',
            'firstname' => 'TEXT',
            'lastname' => 'TEXT',
            'email' => 'TEXT',
            'password' => 'TEXT',
            'is_active' => 'INTEGER',
        ]);

        $this->assertCount(1, $platform->listTables());
        $platform->dropTable('user');
        $this->assertCount(0, $platform->listTables());
    }

    public function testDropColumn()
    {
        $platform = $this->manager->createDatabasePlatform();
        $platform->createTable('user', [
            'id' => 'INTEGER PRIMARY KEY',
            'firstname' => 'TEXT',
            'lastname' => 'TEXT',
            'email' => 'TEXT',
            'password' => 'TEXT',
            'is_active' => 'INTEGER',
        ]);

        $this->assertCount(6, $platform->listTableColumns('user'));
        $platform->dropColumn('user', 'lastname');
        $this->assertCount(5, $platform->listTableColumns('user'));
    }

    public function testAddColumn()
    {
        $platform = $this->manager->createDatabasePlatform();
        $platform->createTable('user', [
            'id' => 'INTEGER PRIMARY KEY',
            'firstname' => 'TEXT',
            'lastname' => 'TEXT',
            'email' => 'TEXT',
            'password' => 'TEXT',
            'is_active' => 'INTEGER',
        ]);

        $this->assertCount(6, $platform->listTableColumns('user'));
        $platform->addColumn('user', 'username', 'TEXT');
        $this->assertCount(7, $platform->listTableColumns('user'));
    }

    public function testRenameColumn()
    {
        $platform = $this->manager->createDatabasePlatform();
        $platform->createTable('user', [
            'id' => 'INTEGER PRIMARY KEY',
            'firstname' => 'TEXT',
            'lastname' => 'TEXT',
            'email' => 'TEXT',
            'password' => 'TEXT',
            'is_active' => 'INTEGER',
        ]);

        $columns = array_column($platform->listTableColumns('user'), 'name');
        $this->assertTrue(in_array('firstname', $columns));

        $platform->renameColumn('user', 'firstname', 'prenom');
        $columns = array_column($platform->listTableColumns('user'), 'name');
        $this->assertTrue(!in_array('firstname', $columns));
        $this->assertTrue(in_array('prenom', $columns));
    }
}
