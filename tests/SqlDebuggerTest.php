<?php

namespace Test\AlphaSoft\AsLinkOrm;

use AlphaSoft\AsLinkOrm\Debugger\SqlDebugger;
use PHPUnit\Framework\TestCase;

class SqlDebuggerTest extends TestCase
{
    public function testSqlDebuggerStartQuery()
    {
        $sqlDebugger = new SqlDebugger();
        $sqlDebugger->startQuery('SELECT * FROM users', []);
        $this->assertArrayHasKey('startTime',$sqlDebugger->getQueries()[0]);
    }

    public function testSqlDebugger()
    {
        $sqlDebugger = new SqlDebugger();
        $sqlDebugger->startQuery('SELECT * FROM users', []);
        $sqlDebugger->stopQuery();
        $queries = $sqlDebugger->getQueries();
        $this->assertCount(1, $queries);
        $this->assertEquals('SELECT * FROM users', $queries[0]['query']);
        $this->assertEquals([], $queries[0]['params']);
        $this->assertNotNull($queries[0]['executionTime']);
    }
}
