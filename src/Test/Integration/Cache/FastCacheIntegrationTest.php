<?php declare(strict_types=1);

namespace SqlException\Base\Test\Integration\Cache;

use PHPUnit\Framework\TestCase;
use SqlException\Base\Cache\FastCache;

class FastCacheIntegrationTest extends TestCase
{
    private $fastCache;

    protected function setUp(): void
    {
        // Initialize FastCache (local array cache)
        $this->fastCache = new FastCache();
    }

    public function testSetAndGet(): void
    {
        // Set a value in FastCache
        $this->fastCache->set('test_key', 'test_value', 3600, ['test_tag']);

        // Retrieve the value from FastCache
        $result = $this->fastCache->get('test_key');
        $this->assertEquals('test_value', $result);
    }

    public function testClear(): void
    {
        // Set a value in FastCache
        $this->fastCache->set('clear_test_key', 'clear_test_value', 3600, ['clear_test_tag']);

        // Ensure the value is set
        $this->assertEquals('clear_test_value', $this->fastCache->get('clear_test_key'));

        // Clear the cache entry
        $this->fastCache->clear('clear_test_key');

        // Ensure the value is now null
        $this->assertNull($this->fastCache->get('clear_test_key'));
    }

    public function testInvalidateByTags(): void
    {
        // Set a value with a tag in FastCache
        $this->fastCache->set('tagged_test_key', 'tagged_test_value', 3600, ['tagged_test_tag']);

        // Ensure the value is set
        $this->assertEquals('tagged_test_value', $this->fastCache->get('tagged_test_key'));

        // Invalidate the cache entry by tag
        $this->fastCache->invalidateByTags(['tagged_test_tag']);

        // Ensure the value is now null
        $this->assertNull($this->fastCache->get('tagged_test_key'));
    }
}
