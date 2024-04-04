<?php

namespace AlphaSoft\AsLinkOrm\Serializer;

use AlphaSoft\AsLinkOrm\Entity\AsEntity;
use AlphaSoft\AsLinkOrm\Mapping\Column;
use AlphaSoft\AsLinkOrm\Types\TypeFactory;

final class SerializerToDb
{
    /**
     * @var AsEntity
     */
    private $entity;

    public function __construct(AsEntity $entity)
    {
        $this->entity = $entity;
    }

    public function serialize(): array
    {
        $entity = $this->entity;
        $dbData = [];
        $attributes = $entity->toArray();
        foreach ($entity::getColumns() as $column) {
            $property = $column->getProperty();
            if (!array_key_exists($property, $attributes)) {
                continue;
            }
            $dbData[sprintf('`%s`', $column->getName())] = $column->convertToDatabase($attributes[$property]);
        }
        return $dbData;
    }

}
