<?php declare(strict_types=1);

namespace SqlException\Base\Test\Integration\Model;

use Magento\Framework\FlagManager;
use Magento\TestFramework\ObjectManager;
use SqlException\Base\Model\ProcessStateFlag;

/**
 * test for Model to have the state of an process as lock flag inside of the database to prevent multiple executions
 */
class ProcessStateFlagTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ProcessStateFlag
     */
    private $flagModel;

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        $this->objectManager = ObjectManager::getInstance();
        $this->flagModel = $this->objectManager->create(ProcessStateFlag::class, ['code' => 'integration_test']);
        $this->flagManager = $this->objectManager->get(FlagManager::class);
    }

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        $this->flagManager->deleteFlag('integration_test');
    }

    /**
     * test standard start stop behavior
     */
    public function testStartStopCycle()
    {
        $this->assertFalse($this->flagModel->isRunning());
        $this->flagModel->start();
        $this->assertTrue($this->flagModel->isRunning());
        $this->flagModel->stop();
        $this->assertFalse($this->flagModel->isRunning());
    }

    /**
     * test destruct on error or exception when process finished
     */
    public function testDestructByMagicMethodCall()
    {
        $flagModel = $this->objectManager->create(ProcessStateFlag::class, ['code' => 'integration_test']);
        $this->assertFalse($flagModel->isRunning());
        $flagModel->start();
        $this->assertTrue($this->flagModel->isRunning());
        $flagModel->__destruct();
        $flagModel = $this->objectManager->create(ProcessStateFlag::class, ['code' => 'integration_test']);
        $this->assertFalse($flagModel->isRunning());
    }

    /**
     * test destruct on error or exception when process finished
     */
    public function testDestructByUnset()
    {
        $flagModel = $this->objectManager->create(ProcessStateFlag::class, ['code' => 'integration_test']);
        $this->assertFalse($flagModel->isRunning());
        $flagModel->start();
        $this->assertTrue($this->flagModel->isRunning());
        unset($flagModel);
        $flagModel = $this->objectManager->create(ProcessStateFlag::class, ['code' => 'integration_test']);
        $this->assertFalse($flagModel->isRunning());
    }

    /**
     * test destruct on error or exception when process finished
     */
    public function testDestructByUnsetAndDifferentPid()
    {
        $flagModel = $this->objectManager->create(ProcessStateFlag::class, ['code' => 'integration_test']);
        $this->assertFalse($flagModel->isRunning());
        $flagModel->start();
        $this->assertTrue($this->flagModel->isRunning());
        $flagModel->setData('pid', '123');
        unset($flagModel);
        $flagModel = $this->objectManager->create(ProcessStateFlag::class, ['code' => 'integration_test']);
        $this->assertTrue($flagModel->isRunning());
    }

    /**
     * test is running with different Pid
     *
     * @magentoDataFixture isRunningDifferentPidFixture
     */
    public function testIsRunningWithDifferentPid()
    {
        $flagModel = $this->objectManager->create(ProcessStateFlag::class, ['code' => 'integration_test']);
        $this->asserttrue($flagModel->isRunning());
        unset($flagModel);
        $flagModel = $this->objectManager->create(ProcessStateFlag::class, ['code' => 'integration_test']);
        $this->assertTrue($flagModel->isRunning());
    }

    /**
     * fixture with different pid
     */
    public static function isRunningDifferentPidFixture()
    {
        $objectManager = ObjectManager::getInstance();
        /** @var FlagManager $flagManager */
        $flagManager = $objectManager->get(FlagManager::class);
        $flagManager->saveFlag('integration_test', "{\"pid\":765,\"state\":1}");
    }
}
