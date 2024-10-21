<?php declare(strict_types=1);

namespace SqlException\Base\Tests\Unit\Cache;

use SqlException\Base\Cache\FastCache;
use PHPUnit\Framework\TestCase;

class FastCacheTest extends TestCase
{
    public function testSetAndGet()
    {
        $cache = new FastCache();
        $cache->set('test_key', 'test_value');
        $this->assertEquals('test_value', $cache->get('test_key'));
    }

    public function testClear()
    {
        $cache = new FastCache();
        $cache->set('key1', 'value1');
        $cache->clear('key1');

        $this->assertNull($cache->get('key1'));
    }

    public function testInvalidateByTags()
    {
        $cache = new FastCache();
        $cache->set('key1', 'value1', null, ['tag1']);
        $cache->set('key2', 'value2', null, ['tag1', 'tag2']);

        $cache->invalidateByTags(['tag1']);

        // Both keys under 'tag1' should be invalidated
        $this->assertNull($cache->get('key1'));
        $this->assertNull($cache->get('key2'));
    }
}
