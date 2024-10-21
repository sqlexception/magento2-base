<?php declare(strict_types=1);

namespace SqlException\Base\Cache;

use SqlException\Base\Api\CacheInterface;

class CacheFacade implements CacheInterface
{
    /**
     * @var FastCache
     */
    private FastCache $fastCache;

    /**
     * @var SlowCache
     */
    private SlowCache $slowCache;

    public function __construct(FastCache $fastCache, SlowCache $slowCache)
    {
        $this->fastCache = $fastCache;
        $this->slowCache = $slowCache;
    }

    /**
     * Retrieve a cached value, first checking FastCache, then falling back to SlowCache.
     *
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        $value = $this->fastCache->get($key);
        if ($value !== null) {
            return $value;
        }

        // Fallback zu SlowCache
        $value = $this->slowCache->get($key);
        if ($value !== null) {
            $this->fastCache->set($key, $value);
        }

        return $value;
    }

    /**
     * Set a value in both FastCache and SlowCache.
     *
     * @param string $key
     * @param mixed $value
     * @param int|null $ttl
     * @param array $tags
     */
    public function set(string $key, $value, ?int $ttl = null, array $tags = []): void
    {
        $this->fastCache->set($key, $value, $ttl, $tags);
        $this->slowCache->set($key, $value, $ttl, $tags);
    }

    /**
     * Clear cache in both FastCache and SlowCache.
     *
     * @param string $key
     */
    public function clear(string $key): void
    {
        $this->fastCache->clear($key);
        $this->slowCache->clear($key);
    }

    /**
     * Invalidate cache entries by tags in both caches.
     *
     * @param array $tags
     */
    public function invalidateByTags(array $tags): void
    {
        $this->slowCache->invalidateByTags($tags);
        $this->fastCache->invalidateByTags($tags);

    }
}
