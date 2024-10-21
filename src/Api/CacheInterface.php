<?php declare(strict_types=1);

namespace SqlException\Base\Api;

interface CacheInterface
{
    /**
     * Retrieve a cached value by key.
     *
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key);

    /**
     * Set a value in the cache with optional time-to-live and tags.
     *
     * @param string $key
     * @param mixed $value
     * @param int|null $ttl Time to live in seconds
     * @param array $tags Cache tags for invalidation
     */
    public function set(string $key, $value, ?int $ttl = null, array $tags = []): void;

    /**
     * Clear a specific cache entry by key.
     *
     * @param string $key
     */
    public function clear(string $key): void;

    /**
     * Invalidate cache entries by tags.
     *
     * @param array $tags
     */
    public function invalidateByTags(array $tags): void;
}
