<?php

namespace AlphaSoft\AsLinkOrm\Mapping;

class Column
{
    /**
     * @var string
     */
    private $property;

    private $type = 'string';

    /**
     * @var mixed
     */
    private $defaultValue = null;

    /**
     * @var string|null
     */
    private $name;

     public function __construct(string $property, $defaultValue = null, string $name = null)
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

    public function getType(): string
    {
        return $this->type;
    }

    final public function type(string $type): self
    {
        $this->type = $type;
        return $this;
    }
}
