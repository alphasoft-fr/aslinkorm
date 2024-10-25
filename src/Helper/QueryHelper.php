<?php

namespace AlphaSoft\AsLinkOrm\Helper;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;

final class QueryHelper
{

    public static function generateWhereQuery(QueryBuilder $query, array $arguments = []): void
    {
        foreach ($arguments as $property => $value) {
            if (is_array($value)) {
                $query->andWhere($query->expr()->in($property, $query->createPositionalParameter($value,  Connection::PARAM_STR_ARRAY)));
                continue;
            }
            if (is_null($value)) {
                $query->andWhere($property . ' IS NULL');
                continue;
            }
            if ($value === '!NULL') {
                $query->andWhere($property . ' IS NOT NULL');
                continue;
            }

            $query->andWhere($property . ' = ' . $query->createPositionalParameter($value));
        }
    }

    public static function typeOfValue($value): int
    {
        $type = ParameterType::STRING;
        if (is_bool($value)) {
            $type = ParameterType::BOOLEAN;
        } elseif (is_int($value)) {
            $type = ParameterType::INTEGER;
        } elseif (is_null($value)) {
            $type = ParameterType::NULL;
        }
        return $type;
    }

}
