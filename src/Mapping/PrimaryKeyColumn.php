<?php

namespace AlphaSoft\AsLinkOrm\Mapping;

use AlphaSoft\AsLinkOrm\Types\IntegerType;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class PrimaryKeyColumn extends Column
{
    public function __construct(string $property, $defaultValue = null, string $name = null, string $type = IntegerType::class)
    {
        parent::__construct($property, $defaultValue, $name, $type);
    }
}
