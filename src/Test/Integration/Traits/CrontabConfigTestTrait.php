<?php
declare(strict_types = 1);

namespace SqlException\Base\Test\Integration\Traits;

use Magento\TestFramework\ObjectManager;

/**
 * standard tests to check a cronjob is successfully configurated
 */
trait CrontabConfigTestTrait
{
    /**
     * test job group exists fron cronjob
     */
    public function testJobGroupExists()
    {
        $config = ObjectManager::getInstance()->create(\Magento\Cron\Model\ConfigInterface::class);
        $jobs = $config->getJobs();
        $this->assertArrayHasKey(
            self::$jobGroup,
            $jobs,
            sprintf('The job group "%s" is not defined.', self::$jobGroup)
        );
    }

    /**
     * test job group exists fron cronjob
     *
     * @depends testJobGroupExists
     */
    public function testCronJobIsRegistered()
    {
        $config = ObjectManager::getInstance()->create(\Magento\Cron\Model\ConfigInterface::class);
        $jobs = $config->getJobs();
        $this->assertArrayHasKey(
            self::$jobName,
            $jobs[self::$jobGroup],
            sprintf('The job "%s" is not defined in job group "%s."', self::$jobName, self::$jobGroup)
        );
    }

    /**
     * test class exists for cronjob
     *
     * @depends testJobGroupExists
     */
    public function testCronJobClassExists()
    {
        $config = ObjectManager::getInstance()->create(\Magento\Cron\Model\ConfigInterface::class);
        $jobs = $config->getJobs();
        $this->assertSame(
            ltrim(self::$jobInstance, '\\'),
            $jobs[self::$jobGroup][self::$jobName]['instance']
        );
    }

    /**
     * test class can be instantiated for cronjob
     *
     * @depends testJobGroupExists
     */
    public function testCronJobClassCanBeInstantiated()
    {
        $config = ObjectManager::getInstance()->create(\Magento\Cron\Model\ConfigInterface::class);
        $jobs = $config->getJobs();
        $jobObject = ObjectManager::getInstance()->get($jobs[self::$jobGroup][self::$jobName]['instance']);
        $this->assertInstanceOf(
            self::$jobInstance,
            $jobObject
        );
    }

    /**
     * test class exists for cronjob
     */
    public function testCronJobMethodExistsInClass()
    {
        $config = ObjectManager::getInstance()->create(\Magento\Cron\Model\ConfigInterface::class);
        $jobs = $config->getJobs();
        $jobObject = ObjectManager::getInstance()->get($jobs[self::$jobGroup][self::$jobName]['instance']);
        $this->assertInstanceOf(
            self::$jobInstance,
            $jobObject
        );
        $this->assertTrue(
            method_exists(
                self::$jobInstance,
                self::$jobMethod
            )
        );
    }
}
