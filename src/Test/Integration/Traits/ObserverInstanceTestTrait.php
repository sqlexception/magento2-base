<?php
declare(strict_types=1);

namespace SqlException\Base\Test\Integration\Traits;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ConfigInterface as EventConfig;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\State as AppState;

/**
 * Trait to test if an observer's instance is correctly registered in events.xml.
 */
trait ObserverInstanceTestTrait
{
    /**
     * Test that the observer is registered for the event in events.xml.
     */
    public function testObserverIsRegisteredInEventsXml(): void
    {
        /** @var EventConfig $eventConfig */
        $eventConfig = ObjectManager::getInstance()->get(EventConfig::class);

        // Get the list of observers for the specified event
        $observers = $eventConfig->getObservers(self::$eventName);

        // Assert that the observer is registered for the event
        $this->assertArrayHasKey(self::$observerName, $observers, sprintf(
            'Observer "%s" is not registered for the event "%s".',
            self::$observerName,
            self::$eventName
        ));
    }

    /**
     * Test that the observer is applied in the correct area (Frontend, Backend, or both).
     */
    public function testObserverIsRegisteredForArea(): void
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

        // Assert that the observer is registered for the correct area
        $this->assertTrue(in_array($areaCode, self::$expectedAreas), sprintf(
            'Observer "%s" is not registered for the area "%s".',
            self::$observerName,
            $areaCode
        ));
    }

    /**
     * Test that the observer's instance is correct.
     * @depends testObserverIsRegisteredInEventsXml
     */
    public function testObserverInstanceIsCorrect(): void
    {
        /** @var EventConfig $eventConfig */
        $eventConfig = ObjectManager::getInstance()->get(EventConfig::class);

        // Get the list of observers for the specified event
        $observers = $eventConfig->getObservers(self::$eventName);

        // Assert that the observer is registered for the event
        $this->assertArrayHasKey(self::$observerName, $observers, sprintf(
            'Observer "%s" is not registered for the event "%s".',
            self::$observerName,
            self::$eventName
        ));

        // Check the instance of the observer
        $observerConfig = $observers[self::$observerName];

        /** @var ObjectManagerInterface $objectManager */
        $objectManager = ObjectManager::getInstance();
        $observerInstance = $objectManager->get($observerConfig['instance']);

        // Assert that the observer instance is of the expected class
        $this->assertInstanceOf(self::$expectedClass, $observerInstance, sprintf(
            'Observer "%s" does not use the expected class "%s".',
            self::$observerName,
            self::$expectedClass
        ));
    }
}
