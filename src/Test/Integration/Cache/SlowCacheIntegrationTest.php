<?php declare(strict_types=1);

namespace SqlException\Base\Test\Integration\Cache;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use SqlException\Base\Cache\SlowCache;

class SlowCacheIntegrationTest extends TestCase
{
    private $slowCache;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        // Get Redis cache from Magento
        $redisCache = $objectManager->get(\Magento\Framework\App\CacheInterface::class);
        // Initialize SlowCache with Redis backend
        $this->slowCache = new SlowCache($redisCache);
    }

    public function testSetAndGet(): void
    {
        // Set a value in SlowCache (Redis)
        $this->slowCache->set('test_key', 'test_value', 3600, ['test_tag']);

        // Retrieve the value from SlowCache
        $result = $this->slowCache->get('test_key');
        $this->assertEquals('test_value', $result);
    }

    public function testClear(): void
    {
        // Set a value in SlowCache
        $this->slowCache->set('clear_test_key', 'clear_test_value', 3600, ['clear_test_tag']);

        // Ensure the value is set
        $this->assertEquals('clear_test_value', $this->slowCache->get('clear_test_key'));

        // Clear the cache entry
        $this->slowCache->clear('clear_test_key');

        // Ensure the value is false
        $this->assertFalse($this->slowCache->get('clear_test_key'));
    }

    public function testInvalidateByTags(): void
    {
        // Set a value with a tag
        $this->slowCache->set('tagged_test_key', 'tagged_test_value', 3600, ['tagged_test_tag']);

        // Ensure the value is set
        $this->assertEquals('tagged_test_value', $this->slowCache->get('tagged_test_key'));

        // Invalidate the cache entry by tag
        $this->slowCache->invalidateByTags(['tagged_test_tag']);

        // Ensure the value is now false
        $this->assertFalse($this->slowCache->get('tagged_test_key'));
    }
}
