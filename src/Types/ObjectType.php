<?php

namespace AlphaSoft\AsLinkOrm\Types;

final class ObjectType extends Type
{

    public function convertToDatabase($value): ?string
    {
        return $value === null ? null : serialize($value);
    }

    public function convertToPHP($value): ?object
    {
        return $value === null ? null : unserialize($value);
    }
}
