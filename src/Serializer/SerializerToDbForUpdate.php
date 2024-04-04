<?php

namespace AlphaSoft\AsLinkOrm\Serializer;

use AlphaSoft\AsLinkOrm\Entity\AsEntity;
use AlphaSoft\AsLinkOrm\Mapping\Column;
use AlphaSoft\AsLinkOrm\Types\TypeFactory;

final class SerializerToDbForUpdate
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
        $modifiedAttributes = $entity->getModifiedAttributes();
        foreach ($entity::getColumns() as $column) {
            $property = $column->getProperty();
            if (!array_key_exists($property, $modifiedAttributes)) {
                continue;
            }
            $dbData[sprintf('`%s`', $column->getName())] = $column->convertToDatabase($modifiedAttributes[$property]);
        }
        return $dbData;
    }
}
