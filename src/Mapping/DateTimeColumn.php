<?php

namespace AlphaSoft\AsLinkOrm\Mapping;

use AlphaSoft\AsLinkOrm\Types\BoolType;
use AlphaSoft\AsLinkOrm\Types\DateTimeType;

#[\Attribute(\Attribute::TARGET_CLASS|\Attribute::IS_REPEATABLE)]
final class DateTimeColumn extends Column
{

    public function __construct(string $property, $defaultValue = null, string $name = null)
    {
        parent::__construct($property, $defaultValue, $name, DateTimeType::class);
    }
}
