<?php

namespace AlphaSoft\AsLinkOrm\Types;

final class BoolType extends Type
{

    public function convertToDatabase($value): ?int
    {
        return $value === null ? null : (int)$value;
    }

    public function convertToPHP($value): ?bool
    {
        return $value === null ? null : (bool)$value;
    }
}
