<?php

namespace AlphaSoft\AsLinkOrm\Mapping;

use AlphaSoft\AsLinkOrm\Types\BoolType;
use AlphaSoft\AsLinkOrm\Types\DecimalType;

#[\Attribute(\Attribute::TARGET_CLASS|\Attribute::IS_REPEATABLE)]
final class DecimalColumn extends Column
{

    public function __construct(
        string                $property,
                              $defaultValue = null,
        private readonly ?int $precision = null,
        private readonly ?int $scale = null,
        string                $name = null
    )
    {
        parent::__construct($property, $defaultValue, $name, DecimalType::class);
    }

    public function getPrecision(): ?int
    {
        return $this->precision;
    }

    public function getScale(): ?int
    {
        return $this->scale;
    }
}
