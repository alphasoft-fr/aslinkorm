<?php

namespace AlphaSoft\AsLinkOrm;

use AlphaSoft\AsLinkOrm\Debugger\SqlDebugger;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;

class AsLinkConnection extends Connection
{
    /**
     * @var null|SqlDebugger
     */
    private ?SqlDebugger $sqlDebugger = null;

    public function enableDebugger(): void
    {
        if ($this->sqlDebugger === null) {
            $this->sqlDebugger = new SqlDebugger();
        }
    }

    public function disableDebugger(): void
    {
        $this->sqlDebugger = null;
    }

    public function executeStatement($sql, array $params = [], array $types = []): int
    {
        $this->sqlDebugger?->startQuery($sql, $params);
        $result = parent::executeStatement($sql,  $params, $types);
        $this->sqlDebugger?->stopQuery();
        return $result;
    }

    public function executeQuery(string $sql, array $params = [], $types = [], ?QueryCacheProfile $qcp = null): Result
    {
        $this->sqlDebugger?->startQuery($sql, $params);
        $result = parent::executeQuery($sql,  $params , $types, $qcp);
        $this->sqlDebugger?->stopQuery();
        return $result;
    }

    public function getSqlDebugger(): ?SqlDebugger
    {
        return $this->sqlDebugger;
    }

    public function getDataBaseName(): ?string
    {
        return $this->getParams()['dbname'] ?? null;
    }
}
