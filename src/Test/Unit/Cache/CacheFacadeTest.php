<?php declare(strict_types=1);

namespace SqlException\Base\Test\Unit\Cache;

use SqlException\Base\Cache\CacheFacade;
use SqlException\Base\Cache\FastCache;
use SqlException\Base\Cache\SlowCache;
use PHPUnit\Framework\TestCase;

class CacheFacadeTest extends TestCase
{
    public function testFallbackToSlowCache(): void
    {
        // Mock für SlowCache mit MockBuilder
        $slowCacheMock = $this->getMockBuilder(SlowCache::class)
            ->disableOriginalConstructor()
            ->getMock();
        $slowCacheMock->expects($this->once())
            ->method('get')
            ->with('test_key')
            ->willReturn('slow_value');

        // Mock für FastCache mit MockBuilder
        $fastCacheMock = $this->getMockBuilder(FastCache::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fastCacheMock->expects($this->once())
            ->method('set')
            ->with('test_key', 'slow_value');

        // Verwenden der CacheFacade mit den Mocks
        $cacheFacade = new CacheFacade($fastCacheMock, $slowCacheMock);
        $result = $cacheFacade->get('test_key');

        // Assertion
        $this->assertEquals('slow_value', $result);
    }

    public function testSetInBothCaches(): void
    {
        // Mock für SlowCache
        $slowCacheMock = $this->getMockBuilder(SlowCache::class)
            ->disableOriginalConstructor()
            ->getMock();
        $slowCacheMock->expects($this->once())
            ->method('set')
            ->with('test_key', 'test_value', 3600);

        // Mock für FastCache
        $fastCacheMock = $this->getMockBuilder(FastCache::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fastCacheMock->expects($this->once())
            ->method('set')
            ->with('test_key', 'test_value', 3600);

        // Verwenden der CacheFacade mit den Mocks
        $cacheFacade = new CacheFacade($fastCacheMock, $slowCacheMock);
        $cacheFacade->set('test_key', 'test_value', 3600);
    }

    public function testInvalidateByTags(): void
    {
        // Mock für SlowCache
        $slowCacheMock = $this->getMockBuilder(SlowCache::class)
            ->disableOriginalConstructor()
            ->getMock();
        $slowCacheMock->expects($this->once())
            ->method('invalidateByTags')
            ->with(['tag1']);

        // Mock für FastCache
        $fastCacheMock = $this->getMockBuilder(FastCache::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fastCacheMock->expects($this->once())
            ->method('invalidateByTags')
            ->with(['tag1']);

        // Verwenden der CacheFacade mit den Mocks
        $cacheFacade = new CacheFacade($fastCacheMock, $slowCacheMock);
        $cacheFacade->invalidateByTags(['tag1']);
    }

    public function testClearCache(): void
    {
        // Mock für SlowCache
        $slowCacheMock = $this->getMockBuilder(SlowCache::class)
            ->disableOriginalConstructor()
            ->getMock();
        $slowCacheMock->expects($this->once())
            ->method('clear')
            ->with('test_key');

        // Mock für FastCache
        $fastCacheMock = $this->getMockBuilder(FastCache::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fastCacheMock->expects($this->once())
            ->method('clear')
            ->with('test_key');

        // Verwenden der CacheFacade mit den Mocks
        $cacheFacade = new CacheFacade($fastCacheMock, $slowCacheMock);
        $cacheFacade->clear('test_key');
    }

    public function testGetFromFastCache(): void
    {
        // Mock für FastCache
        $fastCacheMock = $this->getMockBuilder(FastCache::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fastCacheMock->expects($this->once())
            ->method('get')
            ->with('test_key')
            ->willReturn('fast_value');

        // Mock für SlowCache - sollte nicht aufgerufen werden
        $slowCacheMock = $this->getMockBuilder(SlowCache::class)
            ->disableOriginalConstructor()
            ->getMock();
        $slowCacheMock->expects($this->never())
            ->method('get');

        // Verwenden der CacheFacade mit den Mocks
        $cacheFacade = new CacheFacade($fastCacheMock, $slowCacheMock);
        $result = $cacheFacade->get('test_key');

        // Assertion
        $this->assertEquals('fast_value', $result);
    }

    public function testSetWithTags(): void
    {
        // Mock für SlowCache
        $slowCacheMock = $this->getMockBuilder(SlowCache::class)
            ->disableOriginalConstructor()
            ->getMock();
        $slowCacheMock->expects($this->once())
            ->method('set')
            ->with('test_key', 'test_value', 3600, ['tag1', 'tag2']);

        // Mock für FastCache
        $fastCacheMock = $this->getMockBuilder(FastCache::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fastCacheMock->expects($this->once())
            ->method('set')
            ->with('test_key', 'test_value', 3600, ['tag1', 'tag2']);

        // Verwenden der CacheFacade mit den Mocks
        $cacheFacade = new CacheFacade($fastCacheMock, $slowCacheMock);
        $cacheFacade->set('test_key', 'test_value', 3600, ['tag1', 'tag2']);
    }
}
