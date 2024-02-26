<?php

namespace AlphaSoft\AsLinkOrm;

use AlphaSoft\AsLinkOrm\Repository\Repository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

class DoctrineManager
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var array<Repository>
     */
    private $repositories = [];

    public function __construct(array $params)
    {
        $this->connection = DriverManager::getConnection($params);
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }

    public function getRepository(string $repository): Repository
    {
        if (!is_subclass_of($repository, Repository::class))  {
            throw new \InvalidArgumentException($repository. ' must be an instance of '.Repository::class);
        }

        if (!isset($this->repositories[$repository])) {
            $this->repositories[$repository] = new $repository($this);
        }
        return  $this->repositories[$repository];
    }

    public function clearAll(): void {
        foreach ($this->repositories as $repository) {
            $repository->clear();
        }
    }
}
