<?php

namespace AlphaSoft\AsLinkOrm\Mapping;

use AlphaSoft\AsLinkOrm\Types\IntType;
#[\Attribute(\Attribute::TARGET_CLASS|\Attribute::IS_REPEATABLE)]
final class IntColumn extends Column
{

    public function __construct(string $property, $defaultValue = null, string $name = null)
    {
        parent::__construct($property, $defaultValue, $name, IntType::class);
    }
}
