<?php

namespace AlphaSoft\AsLinkOrm\Mapper;

use AlphaSoft\AsLinkOrm\Cache\ColumnCache;
use AlphaSoft\AsLinkOrm\Cache\OneToManyCache;
use AlphaSoft\AsLinkOrm\Cache\PrimaryKeyColumnCache;
use AlphaSoft\AsLinkOrm\Entity\AsEntity;
use AlphaSoft\AsLinkOrm\Mapping\Column;
use AlphaSoft\AsLinkOrm\Mapping\JoinColumn;
use AlphaSoft\AsLinkOrm\Mapping\OneToMany;
use AlphaSoft\AsLinkOrm\Mapping\PrimaryKeyColumn;
use InvalidArgumentException;
use LogicException;

final class ColumnMapper
{

    static public function getPrimaryKeyColumn(string $class): string
    {
        $cache = PrimaryKeyColumnCache::getInstance();
        if (empty($cache->get($class))) {

            $columnsFiltered = array_filter(self::getColumns($class), function (Column $column) {
                return $column instanceof PrimaryKeyColumn;
            });

            if (count($columnsFiltered) === 0) {
                throw new LogicException(self::class . ' At least one primary key is required. : ' . $class);
            }

            if (count($columnsFiltered) > 1) {
                throw new LogicException(self::class . ' Only one primary key is allowed. : ' . $class);
            }

            $primaryKey = $columnsFiltered[0];

            $cache->set($class, $primaryKey);
        }
        return $cache->get($class)->getName();
    }

    static public function getColumns(string $class): array
    {
        if (!is_subclass_of($class, AsEntity::class)) {
            throw new InvalidArgumentException(self::class . sprintf(' : %s must be subclass of %s', $class, AsEntity::class));
        }

        $cache = ColumnCache::getInstance();
        if (empty($cache->get($class))) {
            self::loadCache($class);
        }
        return $cache->get($class);
    }

    /**
     * @return array<JoinColumn>
     */
    static public function getJoinColumns(string $class): array
    {
        $joinColumns = [];
        foreach (self::getColumns($class) as $column) {
            if ($column instanceof JoinColumn) {
                $joinColumns[] = $column;
            }
        }
        return $joinColumns;
    }

    final static public function getOneToManyRelations(string $class): array
    {
        $cache = OneToManyCache::getInstance();
        if (empty($cache->get($class))) {
            self::loadCache($class);
        }

        return $cache->get($class);
    }

    static public function getColumnByProperty(string $class, string $property): ?Column
    {
        $columns = self::getColumns($class);
        foreach ($columns as $column) {
            if ($column->getProperty() === $property) {
                return $column;
            }
        }
        return null;
    }

    static private function loadCache(string $class): void
    {
        if (!is_subclass_of($class, AsEntity::class)) {
            throw new InvalidArgumentException(self::class . sprintf(' : %s must be subclass of %s', $class, AsEntity::class));
        }

        $columnsMapping = $class::columnsMapping();

        $oneToManyRelations = array_filter($columnsMapping, function (object $column) {
            return $column instanceof OneToMany;
        });
        OneToManyCache::getInstance()->set($class, $oneToManyRelations);

        $columns = array_filter($columnsMapping, function (object $column) {
            return $column instanceof Column;
        });
        ColumnCache::getInstance()->set($class, $columns);
    }
}
