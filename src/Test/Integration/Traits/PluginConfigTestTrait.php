<?php
declare(strict_types=1);

namespace SqlException\Base\Test\Integration\Traits;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Interception\PluginListInterface;
use Magento\Framework\App\State as AppState;

/**
 * Trait to test if a plugin is correctly registered for a class and method.
 */
trait PluginConfigTestTrait
{
    /**
     * Test that the plugin is registered for the specified class and method.
     */
    public function testPluginIsRegisteredForClassAndMethod(): void
    {
        /** @var PluginListInterface $pluginList */
        $pluginList = ObjectManager::getInstance()->get(PluginListInterface::class);

        // Get the list of plugins for the specified class and method
        $plugins = $pluginList->getNext(self::$targetClass, self::$targetMethod);

        // Assert that the plugin is registered for the class and method
        $this->assertArrayHasKey(self::$pluginName, $plugins, sprintf(
            'Plugin "%s" is not registered for the method "%s" of the class "%s".',
            self::$pluginName,
            self::$targetMethod,
            self::$targetClass
        ));
    }

    /**
     * Test that the plugin is applied in the correct area (Frontend, Backend, or both).
     */
    public function testPluginIsRegisteredForArea(): void
    {
        /** @var AppState $appState */
        $appState = ObjectManager::getInstance()->get(AppState::class);

        try {
            // Get the current area code (frontend, adminhtml, etc.)
            $areaCode = $appState->getAreaCode();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->markTestSkipped('Area code is not set.');
            return;
        }

        // Assert that the plugin is registered for the correct area
        $this->assertTrue(in_array($areaCode, self::$expectedAreas), sprintf(
            'Plugin "%s" is not registered for the area "%s".',
            self::$pluginName,
            $areaCode
        ));
    }

    /**
     * Test that the plugin modifies the target method as expected.
     * @depends testPluginIsRegisteredForClassAndMethod
     */
    public function testPluginModifiesMethodAsExpected(): void
    {
        // Create an instance of the target class
        $objectManager = ObjectManager::getInstance();
        $targetInstance = $objectManager->create(self::$targetClass);

        // Call the target method with the arguments
        $result = call_user_func_array([$targetInstance, self::$targetMethod], self::$methodArgs);

        // Assert that the result is as expected after the plugin modification
        $this->assertEquals(self::$expectedResult, $result, sprintf(
            'Plugin "%s" did not modify the method "%s" of the class "%s" as expected.',
            self::$pluginName,
            self::$targetMethod,
            self::$targetClass
        ));
    }
}
