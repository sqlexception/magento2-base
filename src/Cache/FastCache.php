<?php declare(strict_types=1);

namespace SqlException\Base\Cache;

use SqlException\Base\Api\CacheInterface;

class FastCache implements CacheInterface
{
    /**
     * @var array
     */
    private array $cache = [];

    /**
     * @var array
     */
    private array $tags = [];

    /**
     * Retrieve a cached value by key.
     *
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        return $this->cache[$key] ?? null;
    }

    /**
     * Set a value in the cache with optional time-to-live and tags.
     *
     * @param string $key
     * @param mixed $value
     * @param int|null $ttl
     * @param array $tags
     */
    public function set(string $key, $value, ?int $ttl = null, array $tags = []): void
    {
        $this->cache[$key] = $value;

        foreach ($tags as $tag) {
            $this->tags[$tag][$key] = true;
        }
    }

    /**
     * Clear a specific cache entry by key.
     *
     * @param string $key
     */
    public function clear(string $key): void
    {
        unset($this->cache[$key]);

        foreach ($this->tags as &$tagKeys) {
            unset($tagKeys[$key]);
        }
    }

    /**
     * Invalidate cache entries by tags.
     *
     * @param array $tags
     */
    public function invalidateByTags(array $tags): void
    {
        foreach ($tags as $tag) {
            if (isset($this->tags[$tag])) {
                foreach ($this->tags[$tag] as $key => $_) {
                    unset($this->cache[$key]);
                }
                unset($this->tags[$tag]);
            }
        }
    }
}
