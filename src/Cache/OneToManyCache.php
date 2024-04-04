<?php

namespace AlphaSoft\AsLinkOrm\Cache;

use AlphaSoft\AsLinkOrm\Mapping\OneToMany;
use AlphaSoft\AsLinkOrm\Mapping\PrimaryKeyColumn;

final class OneToManyCache
{
    private static $instance;
    private $data = [];

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function set(string $key, array $oneToManyRelations)
    {
        foreach ($oneToManyRelations as $oneToManyRelation) {
            if (!$oneToManyRelation instanceof OneToMany) {
                throw new \InvalidArgumentException(self::class.' - All values in the array must be instances of OneToMany.');
            }
        }

        $this->data[$key] = $oneToManyRelations;
    }

    public function get(string $key): array
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }
        return [];
    }
}
