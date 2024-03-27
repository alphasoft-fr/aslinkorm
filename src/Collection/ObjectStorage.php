<?php

namespace AlphaSoft\AsLinkOrm\Collection;

class ObjectStorage extends \SplObjectStorage
{
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


}
