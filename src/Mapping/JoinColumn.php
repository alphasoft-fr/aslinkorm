<?php

namespace AlphaSoft\AsLinkOrm\Mapping;

use AlphaSoft\AsLinkOrm\Entity\AsEntity;
use AlphaSoft\AsLinkOrm\Types\IntegerType;
use ReflectionClass;

final class JoinColumn extends Column
{
    /**
     * @var string
     */
    private $fictiveProperty;

    /**
     * @var string
     */
    private $referencedColumnName;
    /**
     * @var string
     */
    private $targetEntity;

    final public function __construct(
        string  $property,
        string  $name,
        string  $referencedColumnName,
        string  $targetEntity
    )
    {
        parent::__construct($name,  null, $name, IntegerType::class);
        $this->referencedColumnName = $referencedColumnName;
        $this->targetEntity = $targetEntity;
        $this->fictiveProperty = $property;
    }

    public function getReferencedColumnName(): string
    {
        return $this->referencedColumnName;
    }

    /**
     * @return class-string<AsEntity>
     */
    public function getTargetEntity(): string
    {
        return $this->targetEntity;
    }

    public function getShortName(): string
    {
        return (new ReflectionClass($this->getTargetEntity()))->getShortName();
    }

    public function getType(): string
    {
        return '\\' . ltrim(parent::getType(), '\\');
    }

    public function getFictiveProperty(): string
    {
        return $this->fictiveProperty;
    }
}
