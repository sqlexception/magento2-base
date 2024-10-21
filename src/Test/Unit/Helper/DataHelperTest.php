<?php declare(strict_types=1);

namespace SqlException\Base\Test\Unit\Helper;

use PHPUnit\Framework\TestCase;
use SqlException\Base\Helper\DataHelper;

class DataHelperTest extends TestCase
{
    public function testReplaceStringInColumn()
    {
        $inputData = [
            ['name' => 'John', 'city' => 'New York'],
            ['name' => 'Jane', 'city' => 'Berlin']
        ];

        $expected = [
            ['name' => 'John', 'city' => 'Los Angeles'],
            ['name' => 'Jane', 'city' => 'Berlin']
        ];

        $result = DataHelper::replaceStringInColumn('New York', 'Los Angeles', 'city', $inputData);
        $this->assertEquals($expected, $result);
    }
}
