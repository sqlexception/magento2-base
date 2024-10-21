<?php declare(strict_types=1);

namespace SqlException\Base\Cache;

use SqlException\Base\Api\CacheInterface;
use Magento\Framework\App\CacheInterface as MagentoCacheInterface;

class SlowCache implements CacheInterface
{
    /**
     * @var MagentoCacheInterface
     */
    private $redisCache;

    public function __construct(MagentoCacheInterface $redisCache)
    {
        $this->redisCache = $redisCache->getFrontend();
    }

    /**
     * Retrieve a cached value by key from Redis.
     *
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        return $this->redisCache->load($key);
    }

    /**
     * Set a value in Redis with optional time-to-live and tags.
     *
     * @param string $key
     * @param mixed $value
     * @param int|null $ttl
     * @param array $tags
     */
    public function set(string $key, $value, ?int $ttl = null, array $tags = []): void
    {
        $this->redisCache->save($value, $key, $tags, $ttl ?? 3600);
    }

    /**
     * Clear a specific cache entry from Redis by key.
     *
     * @param string $key
     */
    public function clear(string $key): void
    {
        $this->redisCache->remove($key);
    }

    /**
     * Invalidate cache entries by tags in Redis.
     *
     * @param array $tags
     */
    public function invalidateByTags(array $tags): void
    {
        $this->redisCache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, $tags);
    }
}
