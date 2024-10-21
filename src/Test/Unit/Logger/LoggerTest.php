<?php declare(strict_types=1);

namespace SqlException\Base\Test\Unit\Logger;

use SqlException\Base\Logger\ExtendedLogLevel;
use SqlException\Base\Logger\Logger;
use SqlException\Base\Config\BaseConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\DriverInterface;
use PHPUnit\Framework\TestCase;


class LoggerTest extends TestCase
{
    private $fileDriverMock;
    private $configMock;
    private $directoryListMock;
    private Logger $logger;
    private string $configPrefix = 'test/logger';

    protected function setUp(): void
    {
        $this->configMock = $this->createMock(BaseConfig::class);
        $this->fileDriverMock = $this->createMock(DriverInterface::class);
        $this->directoryListMock = $this->createMock(DirectoryList::class);

        $this->directoryListMock->method('getPath')
            ->with(DirectoryList::LOG)
            ->willReturn('log');

        $this->logger = new Logger(
            $this->configMock,
            $this->fileDriverMock,
            $this->directoryListMock,
            $this->configPrefix
        );
    }

    /**
     * Sets up the Config mock to return specific values for log level, log file, and context logging.
     */
    private function setUpConfigMock(string $logLevel, string $logFile, bool $logContext): void
    {
        // Ensure you mock getValue() for both log level and log file
        $this->configMock->method('getValue')
            ->willReturnMap([
                ['test/logger/' . ExtendedLogLevel::XML_PATH_SUFFIX_LOG_LEVEL, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, $logLevel],
                ['test/logger/' . ExtendedLogLevel::XML_PATH_SUFFIX_LOG_FILE, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, $logFile],
            ]);

        // Mock isSetFlag for the log context
        $this->configMock->method('isSetFlag')
            ->willReturnMap([
                ['test/logger/' . ExtendedLogLevel::XML_PATH_SUFFIX_LOG_CONTEXT, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, $logContext],
            ]);
    }


    /**
     * Data provider for testing different log levels and whether they should be logged.
     */
    public function logLevelProvider(): array
    {
        return [
            ['debug', ExtendedLogLevel::DEBUG, 'Test debug message', true],
            ['info', ExtendedLogLevel::DEBUG, 'Test info message', true],
            ['error', ExtendedLogLevel::DEBUG, 'Test error message', true],
            ['debug', ExtendedLogLevel::ERROR, 'Test debug message', false],
            ['info', ExtendedLogLevel::ERROR, 'Test info message', false],
            ['error', ExtendedLogLevel::ERROR, 'Test error message', true],
        ];
    }

    /**
     * @dataProvider logLevelProvider
     */
    public function testLogMessagesByLevel($logMethod, $configLogLevel, $message, $shouldLog): void
    {
        $this->setUpConfigMock($configLogLevel, 'test/unit.log', false);

        // Simulate file conditions (file exists)
        $this->fileDriverMock->method('isDirectory')->willReturn(true);
        $this->fileDriverMock->method('isExists')->willReturn(true);

        if ($shouldLog) {
            $this->fileDriverMock->expects($this->once())
                ->method('filePutContents')
                ->with(
                    'log/test/unit.log',
                    $this->stringContains(strtoupper($logMethod) . ': ' . $message)
                );
        } else {
            $this->fileDriverMock->expects($this->never())
                ->method('filePutContents');
        }

        // Call the log method dynamically (e.g., debug, info, error)
        $this->logger->{$logMethod}($message);
    }


    /**
     * Data provider for file and directory creation with exceptions.
     */
    public function fileAndDirectoryCreationProvider(): array
    {
        return [
            // Scenario 1: Directory does not exist, needs to be created
            [false, false, false], // directory does not exist, file does not exist
            // Scenario 2: Directory exists, file does not exist, should be created
            [true, false, false],  // directory exists, file does not exist
            // Scenario 3: Exception while creating directory
            [false, false, true],  // directory creation throws exception
            // Scenario 4: Exception while creating file
            [true, false, true],   // file creation throws exception
        ];
    }

    /**
     * @dataProvider fileAndDirectoryCreationProvider
     */
    public function testFileAndDirectoryCreation($directoryExists, $fileExists, $shouldThrowException): void
    {
        $this->setUpConfigMock('debug', 'test/unit.log', false);

        $this->fileDriverMock->method('isDirectory')
            ->willReturn($directoryExists);
        $this->fileDriverMock->method('isExists')
            ->willReturn($fileExists);

        if (!$directoryExists && !$shouldThrowException) {
            $this->fileDriverMock->expects($this->once())
                ->method('createDirectory')
                ->with('log/test', 0755);
        }

        if ($shouldThrowException) {
            // Expect exception to be thrown when trying to create the directory or write the file
            $this->fileDriverMock->method('createDirectory')
                ->willReturn(true); // Directory exists, so exception should occur in filePutContents

            $this->fileDriverMock->method('filePutContents')
                ->willThrowException(new \Magento\Framework\Exception\FileSystemException(
                    new \Magento\Framework\Phrase('Could not create file')
                ));

            // Expect the exception to be thrown
            $this->expectException(\Magento\Framework\Exception\FileSystemException::class);
        }

        if (!$fileExists && !$shouldThrowException) {
            $this->fileDriverMock->expects($this->exactly(2))
                ->method('filePutContents')
                ->withConsecutive(
                    ['log/test/unit.log', ''],  // First call creates an empty file
                    ['log/test/unit.log', $this->stringContains('DEBUG: Test message')]  // Second call writes the log message
                );
        } else if ($fileExists && !$shouldThrowException) {
            $this->fileDriverMock->expects($this->once())
                ->method('filePutContents')
                ->with('log/test/unit.log', $this->stringContains('DEBUG: Test message'));
        }

        $this->logger->debug('Test message');
    }


    /**
     * Data provider for testing interpolation of messages with placeholders.
     */
    public function messageInterpolationProvider(): array
    {
        return [
            ['User {user_id} logged in.', ['user_id' => 123], 'User 123 logged in.'],
            ['No placeholders.', [], 'No placeholders.'],
            ['Missing {user_id} placeholder.', [], 'Missing {user_id} placeholder.'],
        ];
    }

    /**
     * @dataProvider messageInterpolationProvider
     */
    public function testInterpolateMessage($message, $context, $expected): void
    {
        // Access the private method using reflection since it's not public
        $reflection = new \ReflectionClass($this->logger);
        $method = $reflection->getMethod('interpolateMessage');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->logger, [$message, $context]);

        $this->assertEquals($expected, $result);
    }
}
