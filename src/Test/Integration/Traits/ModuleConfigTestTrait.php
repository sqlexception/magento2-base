<?php declare(strict_types=1);

namespace SqlException\Base\Test\Integration\Traits;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\DeploymentConfig\Reader as DeploymentConfigReader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Module\ModuleList;
use Magento\TestFramework\ObjectManager;

/**
 * standard tests to check a module is successfully configurated
 */
trait ModuleConfigTestTrait
{
    /**
     * @var string current module name to test
     */
    public static $moduleName;

    /**
     * Set up before all tests in this trait.
     */
    public static function setUpBeforeClass(): void
    {
        if (!isset(self::$moduleName) || empty(self::$moduleName)) {
            self::$moduleName = static::getDefaultModuleName();
        }
    }

    /**
     * test module is registered
     */
    public function testModuleIsRegistered(): void
    {
        $registrar = new ComponentRegistrar();
        $this->assertArrayHasKey(self::$moduleName, $registrar->getPaths(ComponentRegistrar::MODULE));
    }

    /**
     * test module is configurated and enabled in the test environment
     * @depends testModuleIsRegistered
     */
    public function testModuleIsConfiguratedAndEnabledInTheTestEnvironment(): void
    {
        /** @var ObjectManager $objectManager */
        $objectManager = ObjectManager::getInstance();

        /** @var ModuleList $moduleList */
        $moduleList = $objectManager->create(ModuleList::class);
        $this->assertTrue(
            $moduleList->has(self::$moduleName),
            sprintf('Module "%s" not enabled in the test environment', self::$moduleName)
        );
    }

    /**
     * test module is configurated and enabled in the real environment
     * @depends testModuleIsRegistered
     */
    public function testModuleIsConfiguratedAndEnabledInTheRealEnvironment(): void
    {
        /** @var ObjectManager $objectManager */
        $objectManager = ObjectManager::getInstance();

        /** @var DirectoryList $dirList */
        $dirList = $objectManager->create(DirectoryList::class, ['root' => BP]);

        /** @var DeploymentConfigReader $configReader */
        $configReader = $objectManager->create(DeploymentConfigReader::class, ['dirList' => $dirList]);

        /** @var DeploymentConfig $deploymentConfig */
        $deploymentConfig = $objectManager->create(DeploymentConfig::class, ['reader' => $configReader]);

        /** @var ModuleList $moduleList */
        $moduleList = $objectManager->create(ModuleList::class, ['config' => $deploymentConfig]);
        $this->assertTrue(
            $moduleList->has(self::$moduleName),
            sprintf(
                'Module "%s" not enabled in the real environment',
                self::$moduleName
            )
        );
    }
}
