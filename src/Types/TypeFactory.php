<?php

namespace AlphaSoft\AsLinkOrm\Types;

final class TypeFactory
{
    /**
     * @param string $typeClass
     * @return Type
     * @throws \ReflectionException
     */
    public static function create(string $typeClass): Type
    {
        $type = (new \ReflectionClass($typeClass))->newInstance();
        if (!$type instanceof Type) {
            throw new \InvalidArgumentException($typeClass. ' must be an instance of '.Type::class);
        }
        return $type;
    }
}
