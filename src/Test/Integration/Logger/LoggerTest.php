<?php declare(strict_types=1);

namespace SqlException\Base\Test\Integration\Logger;

use SqlException\Base\Logger\Logger;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for the Logger class with context handling
 */
class LoggerTest extends TestCase
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var FileDriver
     */
    private $fileDriver;

    /**
     * @var string
     */
    protected ?string $logFile = 'integration_test.log';

    /**
     * Setup method
     */
    protected function setUp(): void
    {
        // Bootstrap Magento to initialize the environment
        $objectManager = Bootstrap::getObjectManager();

        // Initialize necessary objects from the Magento framework
        $this->directoryList = $objectManager->get(DirectoryList::class);
        $this->fileDriver = $objectManager->get(FileDriver::class);

        // Set up the logger using the actual Magento configuration
        $this->logger = $objectManager->create(Logger::class, [
            'configPrefix' => 'sqlexception/base',  // Configuration prefix
        ]);
    }

    /**
     * Test that the logger writes the context to the file when enabled
     *
     * @magentoConfigFixture default/sqlexception/base/log_level debug
     * @magentoConfigFixture default/sqlexception/base/log_file integration_test.log
     * @magentoConfigFixture default/sqlexception/base/log_context 1
     */
    public function testLogContextIsWrittenToFile(): void
    {
        $logPath = $this->directoryList->getPath(DirectoryList::LOG) . '/' . $this->logFile;

        // Ensure the log file does not exist before the test
        if ($this->fileDriver->isExists($logPath)) {
            $this->fileDriver->deleteFile($logPath);
        }

        // Log a message with context
        $this->logger->info('Integration test log message', ['test_key' => 'test_value']);

        // Assert that the file was created
        $this->assertTrue($this->fileDriver->isExists($logPath), 'Log file was not created.');

        // Read the log file content
        $logContent = $this->fileDriver->fileGetContents($logPath);

        // Assert that the context is present in the log
        $this->assertStringContainsString('Integration test log message', $logContent);
        $this->assertStringContainsString('[context: {"test_key":"test_value"}]', $logContent, 'Log context not found in the log file.');
    }

    /**
     * Test that the logger writes the message to the file without context
     *
     * @magentoConfigFixture default/sqlexception/base/log_level info
     * @magentoConfigFixture default/sqlexception/base/log_file integration_test_no_context.log
     * @magentoConfigFixture default/sqlexception/base/log_context 0
     */
    public function testLogWithoutContext(): void
    {
        $logPath = $this->directoryList->getPath(DirectoryList::LOG) . '/integration_test_no_context.log';

        // Ensure the log file does not exist before the test
        if ($this->fileDriver->isExists($logPath)) {
            $this->fileDriver->deleteFile($logPath);
        }

        // Log a message without context
        $this->logger->info('Test log message without context');

        // Assert that the file was created
        $this->assertTrue($this->fileDriver->isExists($logPath), 'Log file was not created.');

        // Read the log file content
        $logContent = $this->fileDriver->fileGetContents($logPath);

        // Assert that the log message is present but without any context
        $this->assertStringContainsString('Test log message without context', $logContent);
        $this->assertStringNotContainsString('[context:', $logContent, 'Context was found in the log when it should not be.');
    }


    /**
     * Test that the logger writes messages for different log levels
     *
     * @magentoConfigFixture default/sqlexception/base/log_level debug
     * @magentoConfigFixture default/sqlexception/base/log_file integration_test_different_levels.log
     */
    public function testLogAtDifferentLevels(): void
    {
        $logPath = $this->directoryList->getPath(DirectoryList::LOG) . '/integration_test_different_levels.log';

        // Ensure the log file does not exist before the test
        if ($this->fileDriver->isExists($logPath)) {
            $this->fileDriver->deleteFile($logPath);
        }

        // Log messages at different levels
        $this->logger->debug('Debug level message');
        $this->logger->info('Info level message');
        $this->logger->error('Error level message');

        // Assert that the file was created
        $this->assertTrue($this->fileDriver->isExists($logPath), 'Log file was not created.');

        // Read the log file content
        $logContent = $this->fileDriver->fileGetContents($logPath);

        // Assert that all log messages are present
        $this->assertStringContainsString('Debug level message', $logContent);
        $this->assertStringContainsString('Info level message', $logContent);
        $this->assertStringContainsString('Error level message', $logContent);
    }


    /**
     * Test that log messages below the configured level are not written
     *
     * @magentoConfigFixture default/sqlexception/base/log_level error
     * @magentoConfigFixture default/sqlexception/base/log_file integration_test_level_filter.log
     */
    public function testLogLevelFiltering(): void
    {
        $logPath = $this->directoryList->getPath(DirectoryList::LOG) . '/integration_test_level_filter.log';

        // Ensure the log file does not exist before the test
        if ($this->fileDriver->isExists($logPath)) {
            $this->fileDriver->deleteFile($logPath);
        }

        // Log messages at different levels
        $this->logger->debug('Debug level message should not be logged');
        $this->logger->info('Info level message should not be logged');
        $this->logger->error('Error level message should be logged');

        // Assert that the file was created
        $this->assertTrue($this->fileDriver->isExists($logPath), 'Log file was not created.');

        // Read the log file content
        $logContent = $this->fileDriver->fileGetContents($logPath);

        // Assert that only the error message is present
        $this->assertStringNotContainsString('Debug level message should not be logged', $logContent);
        $this->assertStringNotContainsString('Info level message should not be logged', $logContent);
        $this->assertStringContainsString('Error level message should be logged', $logContent);
    }

    /**
     * Test that context is not written to the log when disabled
     *
     * @magentoConfigFixture default/sqlexception/base/log_level debug
     * @magentoConfigFixture default/sqlexception/base/log_file integration_test_no_context.log
     * @magentoConfigFixture default/sqlexception/base/log_context 0
     */
    public function testLogContextIsNotWrittenToFile(): void
    {
        $logPath = $this->directoryList->getPath(DirectoryList::LOG) . '/integration_test_no_context.log';

        // Ensure the log file does not exist before the test
        if ($this->fileDriver->isExists($logPath)) {
            $this->fileDriver->deleteFile($logPath);
        }

        // Log a message with context, but context should not be logged
        $this->logger->info('Integration test log message', ['test_key' => 'test_value']);

        // Assert that the file was created
        $this->assertTrue($this->fileDriver->isExists($logPath), 'Log file was not created.');

        // Read the log file content
        $logContent = $this->fileDriver->fileGetContents($logPath);

        // Assert that the context is not present in the log
        $this->assertStringContainsString('Integration test log message', $logContent);
        $this->assertStringNotContainsString('context', $logContent, 'Context was found in the log when it should not be.');
    }

    /**
     * Test that the logger only writes to valid log paths under var/log/
     *
     * @magentoConfigFixture default/sqlexception/base/log_level debug
     * @magentoConfigFixture default/sqlexception/base/log_file invalid/../../../outside.log
     */
    public function testInvalidLogFilePath(): void
    {
        $logPath = $this->directoryList->getPath(DirectoryList::LOG) . '/outside.log';

        // Try logging a message with an invalid log path
        $this->expectException(\Magento\Framework\Exception\FileSystemException::class);
        $this->logger->info('This log message should not be written due to invalid path');

        // Assert that the file was not created
        $this->assertFalse($this->fileDriver->isExists($logPath), 'Log file was created with an invalid path.');
    }


    /**
     * Clean up after the test
     */
    protected function tearDown(): void
    {
        $logPath = $this->directoryList->getPath(DirectoryList::LOG) . '/' . $this->logFile;
        if ($this->fileDriver->isExists($logPath)) {
            $this->fileDriver->deleteFile($logPath); // Clean up the log file after the test
        }
    }
}
