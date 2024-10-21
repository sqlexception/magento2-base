<?php declare(strict_types=1);

namespace SqlException\Base\Test\Unit\Helper;

use PHPUnit\Framework\TestCase;
use SqlException\Base\Helper\UUIDHelper;

class UUIDHelperTest extends TestCase
{
    public function testGenerateUuid()
    {
        $uuid = UUIDHelper::generateUuid();
        $this->assertMatchesRegularExpression('/^[a-f0-9\-]+$/', $uuid);
    }

    public function testGenerateRandomString()
    {
        $randomString = UUIDHelper::generateRandomString(8);
        $this->assertEquals(8, strlen($randomString));
    }
}
