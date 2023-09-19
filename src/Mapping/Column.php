<?php

namespace AlphaSoft\AsLinkOrm\Mapping;

class Column
{
    /**
     * @var string
     */
    private $property;

    /**
     * @var mixed
     */
    private $defaultValue = null;

    /**
     * @var string|null
     */
    private $name;

    final public function __construct(string $property, $defaultValue = null, string $name = null)
    {
        $this->property = $property;
        $this->defaultValue = $defaultValue;
        $this->name = $name;
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
}
