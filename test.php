<?php

use AlphaSoft\AsLinkOrm\Command\ShowTablesCommand;
use AlphaSoft\AsLinkOrm\Driver\SqliteDriver;
use AlphaSoft\AsLinkOrm\EntityManager;
use Symfony\Component\Console\Application;

require_once __DIR__.'/vendor/autoload.php';

const ASSQL_HOST = '192.168.33.12';
const ASSQL_PORT = 5001;
const ASSQL_DBNAME = '/dbs/rungis';
const ASSQL_USERNAME = 'alpha';

$manager = new EntityManager([
    'dbname' => ASSQL_DBNAME,
    'user' => ASSQL_USERNAME,
    'password' => null,
    'host' => ASSQL_HOST,
    'port' => ASSQL_PORT,
    'driverClass' => \AlphaSoft\AsLinkOrm\Driver\AssqlDriver::class,
    'driverOptions' => [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
    ]
]);

$application = new Application();
$application->add(new ShowTablesCommand($manager->createDatabasePlatform()));
$application->add(new \AlphaSoft\AsLinkOrm\Command\ShowColumnsTableCommand($manager->createDatabasePlatform()));
$application->add(new \AlphaSoft\AsLinkOrm\Command\ExecuteQueryCommand($manager));
$application->add(new \AlphaSoft\AsLinkOrm\Command\DatabaseSearchCommand($manager));
$application->run();
exit();