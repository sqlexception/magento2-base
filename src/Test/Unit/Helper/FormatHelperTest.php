<?php declare(strict_types=1);

namespace SqlException\Base\Test\Unit\Helper;

use PHPUnit\Framework\TestCase;
use SqlException\Base\Helper\FormatHelper;

class FormatHelperTest extends TestCase
{
    public function testCamelCaseToSnakeCase()
    {
        $result = FormatHelper::camelCaseToSnakeCase('camelCaseString');
        $this->assertEquals('camel_case_string', $result);
    }

    public function testFormatString()
    {
        $result = FormatHelper::formatString('Hello, {name}!', ['{name}' => 'John']);
        $this->assertEquals('Hello, John!', $result);
    }
}
