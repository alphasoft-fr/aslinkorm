<?php

namespace AlphaSoft\AsLinkOrm\Mapping;

use AlphaSoft\AsLinkOrm\Types\Type;
use AlphaSoft\AsLinkOrm\Types\TypeFactory;

#[\Attribute(\Attribute::TARGET_CLASS|\Attribute::IS_REPEATABLE)]
class Column
{

     public function __construct(
         private readonly string $property,
         private $defaultValue = null,
         private readonly ?string $name = null,
         private string $type = 'string',
         private readonly bool $unique = false,
         private readonly bool $nullable = false,
     )
    {
    }

    final public function __toString(): string
    {
        return $this->getProperty();
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    final public function getName(): ?string
    {
        return $this->name ?: $this->getProperty();
    }

    /**
     * @return mixed|null
     */
    final public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function getType(): string
    {
        return $this->type;
    }

    final public function type(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function isUnique(): bool
    {
        return $this->unique;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * Converts a value to its corresponding database representation.
     *
     * @param mixed $value The value to be converted.
     * @return mixed The converted value.
     * @throws \ReflectionException
     */
    final function convertToDatabase($value)
    {
        $type = $this->getType();
        if (is_subclass_of($type, Type::class)) {
            $value = TypeFactory::create($type)->convertToDatabase($value);
        }
        return $value;
    }

    /**
     * Converts a value to its corresponding PHP representation.
     *
     * @param mixed $value The value to be converted.
     * @return mixed The converted PHP value.
     * @throws \ReflectionException
     */
    final function convertToPHP($value)
    {
        $type = $this->getType();
        if (is_subclass_of($type, Type::class)) {
            $value = TypeFactory::create($type)->convertToPHP($value);
        }
        return $value;
    }
}
