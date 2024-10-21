<?php
declare(strict_types = 1);

namespace SqlException\Base\Test\Integration\Traits;

use Magento\TestFramework\ObjectManager;

/**
 * standard tests to check a cli command is successfully configurated
 * @TestCase
 * @method assertArrayHasKey($commandCode, \Symfony\Component\Console\Command\Command[] $commands, string $sprintf)
 */
trait CommandConfigTestTrait
{
    /**
     * test command is successfully registered
     */
    public function testCommandIsRegistered()
    {
        $commandConfig = ObjectManager::getInstance()->create(\Magento\Framework\Console\CommandListInterface::class);
        $commands = $commandConfig->getCommands();

        $this->assertArrayHasKey(
            self::$commandCode,
            $commands,
            sprintf('The command "%s" is not defined.', self::$commandName)
        );
    }

    /**
     * @depends testCommandIsRegistered
     */
    public function testCommandNameIsSet()
    {
        $commandConfig = ObjectManager::getInstance()->create(\Magento\Framework\Console\CommandListInterface::class);
        $commands = $commandConfig->getCommands();

        $this->assertNotEmpty($commands[self::$commandCode]->getName());
        $this->assertEquals(self::$commandName, $commands[self::$commandCode]->getName());
    }

    /**
     * @depends testCommandIsRegistered
     */
    public function testCommandDescriptionIsSet()
    {
        $commandConfig = ObjectManager::getInstance()->create(\Magento\Framework\Console\CommandListInterface::class);
        $commands = $commandConfig->getCommands();

        $this->assertNotEmpty($commands[self::$commandCode]->getDescription());
        $this->assertEquals(self::$commandDescription, $commands[self::$commandCode]->getDescription());
    }
}
