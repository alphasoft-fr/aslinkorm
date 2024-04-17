<?php

namespace AlphaSoft\AsLinkOrm\Mapping;

use AlphaSoft\AsLinkOrm\Types\BoolType;
use AlphaSoft\AsLinkOrm\Types\DateTimeType;
use AlphaSoft\AsLinkOrm\Types\DateType;

#[\Attribute(\Attribute::TARGET_CLASS|\Attribute::IS_REPEATABLE)]
final class DateColumn extends Column
{

    public function __construct(string $property, $defaultValue = null, string $name = null)
    {
        parent::__construct($property, $defaultValue, $name, DateType::class);
    }
}
