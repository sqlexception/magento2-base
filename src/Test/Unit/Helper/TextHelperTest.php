<?php declare(strict_types=1);

namespace SqlException\Base\Test\Unit\Helper;

use PHPUnit\Framework\TestCase;
use SqlException\Base\Helper\TextHelper;

class TextHelperTest extends TestCase
{
    public function testTruncateString()
    {
        $result = TextHelper::truncateString('Hello World', 5);
        $this->assertEquals('Hello...', $result);
    }

    public function testTruncateStringWithHtmlSuffix()
    {
        $result = TextHelper::truncateString('Hello World', 5, '&hellip;');
        $this->assertEquals('Hello&hellip;', $result);
    }

    public function testSlugify()
    {
        $result = TextHelper::slugify('Hello World!');
        $this->assertEquals('hello-world', $result);
    }
}
