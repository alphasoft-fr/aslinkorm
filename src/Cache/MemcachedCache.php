<?php

namespace AlphaSoft\AsLinkOrm\Cache;


class MemcachedCache
{
    /**
     * @var array<object>
     */
    private $cache = [];


    public function get(string $key): ?object
    {
        if ($this->has($key)) {
            return $this->cache[$key];
        }

        return null;
    }

    public function has(string $key): bool
    {
        return isset($this->cache[$key]);
    }

    public function set(string $key, object $value): void
    {
        $this->cache[$key] = $value;
    }

    public function invalidate(string $key): void
    {
        unset($this->cache[$key]);
    }

    public function clear(): void
    {
        $this->cache = [];
    }
}
