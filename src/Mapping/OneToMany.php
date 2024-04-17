<?php

namespace AlphaSoft\AsLinkOrm\Mapping;

use AlphaSoft\AsLinkOrm\Collection\ObjectStorage;

#[\Attribute(\Attribute::TARGET_CLASS|\Attribute::IS_REPEATABLE)]
final class OneToMany
{
    /**
     * @var string
     */
    private $property;

    /**
     * @var string
     */
    private $targetEntity;
    /**
     * @var array
     */
    private $criteria;

    /**
     * @var \SplObjectStorage
     */
    private $storage;
    final public function __construct(string $property, string $targetEntity, array $criteria = [])
    {
        $this->property = $property;
        $this->targetEntity = $targetEntity;
        $this->criteria = $criteria;
        $this->storage = new ObjectStorage();
    }

    public function getTargetEntity(): string
    {
        return $this->targetEntity;
    }

    public function getCriteria(): array
    {
        return $this->criteria;
    }

    public function getDefaultValue():ObjectStorage
    {
        return clone $this->storage;
    }
    public function getShortName(): string
    {
        return (new \ReflectionClass($this->getTargetEntity()))->getShortName();
    }
    public function getType(): string
    {
        return '\\'.ltrim(get_class($this->getDefaultValue()), '\\');
    }

    public function getProperty(): string
    {
        return $this->property;
    }
}
