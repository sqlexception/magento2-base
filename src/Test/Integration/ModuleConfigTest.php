<?php
declare(strict_types = 1);

namespace SqlException\Base\Test\Integration;

use SqlException\Base\Test\Integration\Traits\ModuleConfigTestTrait;

/**
 * Integration Test Module SqlException_Base is registered
 */
class ModuleConfigTest extends \PHPUnit\Framework\TestCase
{
    use ModuleConfigTestTrait;

    /**
     * Get the default module name for the current test case.
     * This method should be overridden by the test class to provide the specific module name.
     *
     * @return string
     */
    protected static function getDefaultModuleName(): string
    {
        return 'SqlException_Base';
    }
}
