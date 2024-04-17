<?php

namespace Test\AlphaSoft\AsLinkOrm;

use AlphaSoft\AsLinkOrm\Command\ShowColumnsTableCommand;
use AlphaSoft\AsLinkOrm\Command\ShowTablesCommand;
use AlphaSoft\AsLinkOrm\Driver\SqliteDriver;
use AlphaSoft\AsLinkOrm\EntityManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class DatabaseShowColumnsTableCommandTest extends TestCase
{
    private EntityManager $manager;

    protected function setUp(): void
    {
        $manager = new EntityManager([
            'driver' => null,
            'driverClass' => SqliteDriver::class,
            'memory' => true,
            'driverOptions' => [\PDO::ATTR_EMULATE_PREPARES => FALSE, \PDO::FETCH_NUM => true]
        ]);

        $this->manager = $manager;
    }

    public function testExecute(): void
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

        $application = new Application();
        $application->add(new ShowColumnsTableCommand($platform));

        $command = $application->find('aslink:show:columns');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['table' => 'user']);
        $commandTester->assertCommandIsSuccessful();
    }
}