<?php

namespace AlphaSoft\AsLinkOrm\Collection;

use SplObjectStorage;

class ObjectStorage extends SplObjectStorage
{

    public function __construct(array $data = [])
    {
        foreach ($data as $item) {
            $this->attach($item);
        }
    }

    public function findPk($pk): ?object
    {
        if ($pk === null) {
            return null;
        }

        foreach ($this as $object) {
            if (method_exists($object, 'getId') && $object->getId() === $pk) {
                return $object;
            }
            if (method_exists($object, 'getPrimaryKeyValue') && $object->getPrimaryKeyValue() === $pk) {
                return $object;
            }
        }
        return null;
    }

    public function findOneBy(string $method, $value): ?object
    {
        foreach ($this as $object) {
            if (method_exists($object, $method) && $object->$method() === $value) {
                return $object;
            }
        }
        return null;

    }

    /**
     * Finds an object in the collection using a callback.
     *
     * @param callable $callback The callback used for searching.
     * @return mixed|null The found object or null if no object matches the criteria.
     */
    public function find(callable $callback)
    {
        foreach ($this as $item) {
            if ($callback($item)) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Finds all objects in the collection that match a given criteria.
     *
     * @param callable $callback The callback used for searching.
     * @return array An array containing all objects that match the criteria.
     */
    public function filter(callable $callback): array
    {
        $foundObjects = [];
        foreach ($this as $item) {
            if ($callback($item)) {
                $foundObjects[] = $item;
            }
        }
        return $foundObjects;
    }

    public function isEmpty(): bool
    {
        return count($this) === 0;
    }

    /**
     * Retrieves the first object in the collection.
     *
     * @return mixed|null The first object or null if the collection is empty.
     */
    public function first()
    {
        foreach ($this as $item) {
            return $item;
        }
        return null;
    }

    /**
     * Converts the collection to an array.
     *
     * @return array The collection converted to an array.
     */
    public function toArray(): array
    {
        return iterator_to_array($this);
    }

    /**
     * Retrieves the last item in the collection.
     *
     * @return mixed|null The last item in the collection, or null if the collection is empty.
     */
    public function last()
    {
        $last = null;
        foreach ($this as $item) {
            $last = $item;
        }
        return $last;
    }

    public function clear(): void
    {
        foreach ($this as $item) {
            $this->detach($item);
        }
    }
}
