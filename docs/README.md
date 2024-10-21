
# 2-Level Cache System for Magento 2

This module provides a 2-level caching system that combines a fast, in-memory cache (FastCache) and a persistent Redis-based cache (SlowCache). The CacheFacade integrates both caches to offer optimal performance, checking the fast cache first before falling back to the slower, persistent Redis cache.

## Features

- **FastCache**: In-memory cache that operates within a single request for quick access.
- **SlowCache (Redis)**: Persistent cache that stores data across requests.
- **CacheFacade**: A wrapper that uses both caches to combine fast access with persistence.
- **Multi-Get**: Fetch multiple cache entries in one call for efficiency.
- **Cache Tags**: Use tags to group cache entries and invalidate related data.

## Installation

1. Download the module and extract it to your `app/code/Vendor/Module` directory.
2. Run the following Magento CLI commands:
   ```bash
   php bin/magento setup:upgrade
   php bin/magento cache:flush
   ```

## Usage

### Using FastCache

FastCache is ideal for in-memory caching that lasts only for the duration of a single request.

```php
use SqlException\Base\Cache\FastCache;

$fastCache = new FastCache();
$cacheKey = 'my_key';

// Set cache
$fastCache->set($cacheKey, 'my_value');

// Get cache
$value = $fastCache->get($cacheKey);

// Multi-Get
$values = $fastCache->multiGet(['key1', 'key2']);
```

### Using SlowCache (Redis)

SlowCache uses Redis to store data persistently.

```php
use SqlException\Base\Cache\SlowCache;
use Magento\Framework\App\CacheInterface;

$slowCache = new SlowCache($redisCache);
$cacheKey = 'my_key';

// Set cache
$slowCache->set($cacheKey, 'my_value', 3600);

// Get cache
$value = $slowCache->get($cacheKey);

// Multi-Get
$values = $slowCache->multiGet(['key1', 'key2']);
```

### Using CacheFacade

The CacheFacade seamlessly integrates FastCache and SlowCache. It checks FastCache first, then falls back to SlowCache if necessary.

```php
use SqlException\Base\Cache\CacheFacade;

$cacheFacade = new CacheFacade($fastCache, $slowCache);
$cacheKey = 'my_key';

// Get from cache, fallback to SlowCache if FastCache misses
$value = $cacheFacade->get($cacheKey);

if ($value === null) {
    $value = 'my_value';
    $cacheFacade->set($cacheKey, $value, 3600, ['tag1']);
}

// Multi-Get
$values = $cacheFacade->multiGet(['key1', 'key2']);

// Invalidate by tags
$cacheFacade->invalidateByTags(['tag1']);
```

## Multi-Get

Use multiGet to retrieve multiple cache entries at once:

```php
$keys = ['key1', 'key2', 'key3'];
$values = $cacheFacade->multiGet($keys);
```

## Cache Tags

Cache entries can be tagged and invalidated by tag:

```php
$cacheFacade->set('product_123', 'product_data', 3600, ['product_tag']);
$cacheFacade->invalidateByTags(['product_tag']);
```

## Testing

Unit tests are available in the `tests/` directory. To run the tests, use PHPUnit:

```bash
vendor/bin/phpunit tests/
```
