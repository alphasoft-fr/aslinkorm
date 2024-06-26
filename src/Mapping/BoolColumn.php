<?php

namespace AlphaSoft\AsLinkOrm\Mapping;

use AlphaSoft\AsLinkOrm\Types\BoolType;

#[\Attribute(\Attribute::TARGET_CLASS|\Attribute::IS_REPEATABLE)]
final class BoolColumn extends Column
{

    public function __construct(string $property, $defaultValue = null, string $name = null)
    {
        parent::__construct($property, $defaultValue, $name, BoolType::class);
    }
}
