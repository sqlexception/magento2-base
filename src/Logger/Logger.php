<?php declare(strict_types=1);

namespace SqlException\Base\Logger;

use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\Phrase;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Exception\FileSystemException;
use SqlException\Base\Config\BaseConfig;

/**
 * Handles logging operations in Magento
 */
class Logger implements LoggerInterface
{
    /**
     * @var BaseConfig
     */
    private $config;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var DriverInterface
     */
    private $fileDriver;

    /**
     * @var string
     */
    private $logLevel;

    /**
     * @var string
     */
    private $logFilePath;

    /**
     * @var string
     */
    private $logFile;

    /**
     * @var bool
     */
    private $logContext;

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @var string
     */
    private $configPrefix;

    /**
     * @var array
     */
    private $logLevels = [
        ExtendedLogLevel::DEBUG => 100,
        ExtendedLogLevel::INFO => 200,
        ExtendedLogLevel::NOTICE => 250,
        ExtendedLogLevel::WARNING => 300,
        ExtendedLogLevel::ERROR => 400,
        ExtendedLogLevel::CRITICAL => 500,
        ExtendedLogLevel::ALERT => 550,
        ExtendedLogLevel::EMERGENCY => 600,
        ExtendedLogLevel::DISABLED => -1,
    ];

    /**
     * Constructor
     *
     * @param BaseConfig $config
     * @param DriverInterface $fileDriver
     * @param DirectoryList $directoryList
     * @param string $configPrefix
     */
    public function __construct(
        BaseConfig $config,
        DriverInterface $fileDriver,
        DirectoryList $directoryList,
        string $configPrefix
    ) {
        $this->config = $config;
        $this->directoryList = $directoryList;
        $this->fileDriver = $fileDriver;
        $this->configPrefix = rtrim($configPrefix, '/');
    }

    /**
     * Initialize the logger
     *
     * @return self
     * @throws FileSystemException
     */
    private function init(): self
    {
        if ($this->initialized) {
            return $this;
        }

        $this->logLevel = $this->getLogLevelFromConfig();
        $this->logFilePath = $this->getLogFilePathFromConfig();
        $this->logContext = $this->getLogContextFromConfig();
        $this->prepareLogFilePathAndFile();

        $this->initialized = true;

        return $this;
    }

    /**
     * Get the full path for the configuration
     *
     * @param string $suffix
     * @return string
     */
    private function getConfigPath(string $suffix): string
    {
        return $this->configPrefix . '/' . $suffix;
    }

    /**
     * Retrieve log context flag from configuration
     *
     * @return bool
     */
    private function getLogContextFromConfig(): bool
    {
        return $this->config->isSetFlag($this->getConfigPath(ExtendedLogLevel::XML_PATH_SUFFIX_LOG_CONTEXT));
    }

    /**
     * Retrieve the log level from configuration
     *
     * @return string
     * @throws InvalidArgumentException
     */
    private function getLogLevelFromConfig(): string
    {
        $logLevel = $this->config->getValue($this->getConfigPath(ExtendedLogLevel::XML_PATH_SUFFIX_LOG_LEVEL));

        if (empty($logLevel)) {
            return ExtendedLogLevel::DISABLED;
        }

        if (!isset($this->logLevels[$logLevel])) {
            throw new InvalidArgumentException(new Phrase('Invalid log level in configuration.'));
        }

        return $logLevel;
    }

    /**
     * Retrieve the log file path from configuration
     *
     * @return string
     */
    private function getLogFilePathFromConfig(): string
    {
        $logFile = $this->config->getValue($this->getConfigPath(ExtendedLogLevel::XML_PATH_SUFFIX_LOG_FILE));

        return empty($logFile) ? 'system.log' : $logFile;
    }

    /**
     * Prepare the log file and directory
     *
     * @throws FileSystemException
     */
    private function prepareLogFilePathAndFile(): void
    {
        $logDir = $this->directoryList->getPath(DirectoryList::LOG);
        $fullLogDir = $this->getFullLogDir($logDir, $this->logFilePath);
        $resolvedFullLogDir = $this->resolveLogDir($fullLogDir, $logDir);

        $this->ensureDirectoryExists($resolvedFullLogDir);
        $this->logFile = $resolvedFullLogDir . '/' . basename($this->logFilePath);
        $this->ensureLogFileExists($this->logFile);
    }

    /**
     * Get the full log directory path
     *
     * @param string $logDir
     * @param string $logFilePath
     * @return string
     */
    private function getFullLogDir(string $logDir, string $logFilePath): string
    {
        $logFileDir = dirname($logFilePath);

        return ($logFileDir === '.' || $logFileDir === '') ? $logDir : $logDir . '/' . $logFileDir;
    }

    /**
     * Resolve the log directory using realpath
     *
     * @param string $fullLogDir
     * @param string $logDir
     * @return string
     * @throws FileSystemException
     */
    private function resolveLogDir(string $fullLogDir, string $logDir): string
    {
        $resolvedFullLogDir = realpath($fullLogDir) ?: $fullLogDir;
        $resolvedLogDir = realpath($logDir) ?: $logDir;

        if ($resolvedFullLogDir === false || $resolvedLogDir === false || !$this->isPathInBaseDir($resolvedFullLogDir, $resolvedLogDir)) {
            throw new FileSystemException(new Phrase('Invalid log directory: %1', [$fullLogDir]));
        }

        return $resolvedFullLogDir;
    }

    /**
     * Ensure directory exists
     *
     * @param string $resolvedFullLogDir
     * @throws FileSystemException
     */
    private function ensureDirectoryExists(string $resolvedFullLogDir): void
    {
        try {
            if (!$this->fileDriver->isDirectory($resolvedFullLogDir)) {
                $this->fileDriver->createDirectory($resolvedFullLogDir, 0755);
            }
        } catch (FileSystemException $e) {
            throw new FileSystemException(new Phrase('Could not create log directory: %1', [$resolvedFullLogDir]), $e);
        }
    }

    /**
     * Ensure log file exists
     *
     * @param string $fullLogFilePath
     * @throws FileSystemException
     */
    private function ensureLogFileExists(string $fullLogFilePath): void
    {
        try {
            if (!$this->fileDriver->isExists($fullLogFilePath)) {
                $this->fileDriver->filePutContents($fullLogFilePath, '');
            }
        } catch (FileSystemException $e) {
            throw new FileSystemException(new Phrase('Could not create log file: %1', [$fullLogFilePath]), $e);
        }
    }

    /**
     * Check if a path is within the base directory
     *
     * @param string $path
     * @param string $baseDir
     * @return bool
     */
    private function isPathInBaseDir(string $path, string $baseDir): bool
    {
        return strpos($path, $baseDir) === 0;
    }

    /**
     * Log a message at a given level
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @throws FileSystemException
     */
    public function log($level, $message, array $context = []): void
    {
        $this->init();

        if ($this->logLevel === ExtendedLogLevel::DISABLED) {
            return;
        }

        if (isset($this->logLevels[$level]) && $this->logLevels[$level] >= $this->logLevels[$this->logLevel]) {
            $formattedMessage = $this->formatMessage($level, $message, $context);
            $this->fileDriver->filePutContents($this->logFile, $formattedMessage . PHP_EOL, FILE_APPEND);
        }
    }

    /**
     * Format log message with level and context
     *
     * @param string $level
     * @param string $message
     * @param array $context
     * @return string
     */
    private function formatMessage(string $level, string $message, array $context = []): string
    {
        $date = (new \DateTime())->format('Y-m-d H:i:s');
        $interpolatedMessage = $this->interpolateMessage($message, $context);
        $contextString = $this->formatContext($context);

        return sprintf("[%s] %s: %s %s", $date, strtoupper($level), $interpolatedMessage, $contextString);
    }

    /**
     * Interpolate placeholders in message with context
     *
     * @param string $message
     * @param array $context
     * @return string
     */
    private function interpolateMessage(string $message, array $context = []): string
    {
        foreach ($context as $key => $value) {
            $message = str_replace('{' . $key . '}', (string)$value, $message);
        }
        return $message;
    }

    /**
     * Format context as a string
     *
     * @param array $context
     * @return string
     */
    private function formatContext(array $context): string
    {
        if ($this->logContext === false || empty($context)) {
            return '';
        }

        return '[context: ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ']';
    }

    /**
     * Log a DEBUG message.
     *
     * @param string $message The log message.
     * @param array $context Optional context information.
     * @return void
     */
    public function debug($message, array $context = []): void
    {
        $this->log(ExtendedLogLevel::DEBUG, $message, $context);
    }

    /**
     * Log an INFO message.
     *
     * @param string $message The log message.
     * @param array $context Optional context information.
     * @return void
     */
    public function info($message, array $context = []): void
    {
        $this->log(ExtendedLogLevel::INFO, $message, $context);
    }

    /**
     * Log a NOTICE message.
     *
     * @param string $message The log message.
     * @param array $context Optional context information.
     * @return void
     */
    public function notice($message, array $context = []): void
    {
        $this->log(ExtendedLogLevel::NOTICE, $message, $context);
    }

    /**
     * Log a WARNING message.
     *
     * @param string $message The log message.
     * @param array $context Optional context information.
     * @return void
     */
    public function warning($message, array $context = []): void
    {
        $this->log(ExtendedLogLevel::WARNING, $message, $context);
    }

    /**
     * Log an ERROR message.
     *
     * @param string $message The log message.
     * @param array $context Optional context information.
     * @return void
     */
    public function error($message, array $context = []): void
    {
        $this->log(ExtendedLogLevel::ERROR, $message, $context);
    }

    /**
     * Log a CRITICAL message.
     *
     * @param string $message The log message.
     * @param array $context Optional context information.
     * @return void
     */
    public function critical($message, array $context = []): void
    {
        $this->log(ExtendedLogLevel::CRITICAL, $message, $context);
    }

    /**
     * Log an ALERT message.
     *
     * @param string $message The log message.
     * @param array $context Optional context information.
     * @return void
     */
    public function alert($message, array $context = []): void
    {
        $this->log(ExtendedLogLevel::ALERT, $message, $context);
    }

    /**
     * Log an EMERGENCY message.
     *
     * @param string $message The log message.
     * @param array $context Optional context information.
     * @return void
     */
    public function emergency($message, array $context = []): void
    {
        $this->log(ExtendedLogLevel::EMERGENCY, $message, $context);
    }
}
