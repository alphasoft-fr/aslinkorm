<?php

namespace AlphaSoft\AsLinkOrm\Mapping;

use AlphaSoft\AsLinkOrm\Types\Type;
use AlphaSoft\AsLinkOrm\Types\TypeFactory;

class Column
{
    /**
     * @var string
     */
    private $property;

    private $type;

    /**
     * @var mixed
     */
    private $defaultValue;

    /**
     * @var string|null
     */
    private $name;

     public function __construct(string $property, $defaultValue = null, string $name = null, string $type = 'string')
    {
        $this->property = $property;
        $this->defaultValue = $defaultValue;
        $this->name = $name;
        $this->type = $type;
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
